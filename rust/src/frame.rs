use std::convert::TryInto;

use crate::utils;

pub struct Response {
    pub message_type: u16,
    pub payload: Vec<u8>,
}

pub fn decode(raw_frame: Vec<u8>) -> Response {
    if raw_frame.len() < 12 {
        panic!("The frame is too short");
    }

    let mut checksum: u16 = 0;

    // msg_type, length, checksum, value, lqi = \
    //     struct.unpack('!HHB%dsB' % (len(decoded) - 6), decoded)

    let mut frame: Vec<u8> = Vec::new();

    let mut flip = false;

    // Check that the first byte is 0x01 and the last is 0x03
    if raw_frame[0] != 0x01 {
        panic!("The frame must start with 0x01");
    }

    if raw_frame[raw_frame.len() - 1] != 0x03 {
        panic!("The frame must end with 0x03");
    }

    let no_head_tail: Vec<u8> = raw_frame.clone().drain(1..raw_frame.len() - 1).collect();

    for byte in no_head_tail {
        if flip {
            flip = false;
            frame.push(byte ^ 0x10)
        } else if byte == 0x02 {
            flip = true;
        } else {
            frame.push(byte);
        }
    }

    let r_message_type = utils::two_u8_to_u16(frame[0], frame[1]);
    let r_length = utils::two_u8_to_u16(frame[2], frame[3]);

    let r_crc = frame[4];
    let r_payload: Vec<u8> = frame.drain(5..).collect();

    println!("frame::decode: payload: {}", utils::pretty_hex(&r_payload));
    println!("frame::decode: expected length: {}", r_length);

    // TODO parse lqi
    // decoded = self.zigate_decode(packet[1:-1])
    // msg_type, length, checksum, value, lqi = \
    //     struct.unpack('!HHB%dsB' % (len(decoded) - 6), decoded)

    if r_payload.len() != r_length.into() {
        // panic!("Payload length does not match the expected length");
        println!("frame::decode !!! Payload length does not match the expected length");
    }

    // TODO check crc
    // println!("r_crc         : {}", utils::pretty_hex(&[r_crc]));

    Response {
        message_type: r_message_type,
        payload: r_payload,
    }
}

pub fn encode(command: (u8, u8), data: Vec<u8>) -> Vec<u8> {
    let data_length: u8 = data.len().try_into().unwrap();
    let checksum = compute_checksum(command, data_length);

    let frame = vec![command.0, command.1, 0, data_length, checksum];

    escape(frame)
}

fn escape(raw: Vec<u8>) -> Vec<u8> {
    let mut result = vec![1];

    for byte in raw {
        match byte {
            0x00..=0x10 => {
                // Encode all characters < 0x10
                result.push(2);
                result.push(0x10 ^ byte);
            }
            _ => {
                result.push(byte);
            }
        }
    }

    result.push(3);
    result
}

// TODO compute checksum with data
fn compute_checksum(command: (u8, u8), length: u8) -> u8 {
    // TODO work with 2 bytes commands
    command.1 ^ length
}
