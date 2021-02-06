use crate::frame;
use crate::utils;

pub fn parse(response: frame::Response) -> String {
    match response.message_type {
        0x8000 => parse_status(response.payload),
        0x8010 => parse_version(response.payload),
        t => panic!("Unknown message type {}", t),
    }
}

fn parse_status(payload: Vec<u8>) -> String {
    println!("response: {}", utils::pretty_hex(&payload));

    let status = payload[0];
    let sequence_number = payload[1];
    let packet_type = [payload[2], payload[3]];
    let errors = &payload[4..];

    if status != 0 {
        let error = match status {
    1 => "status is Incorrect parameters",
    2 => "Unhandled command",
    3 => "Command failed",
    4 => "Busy (Node is carrying out a lengthy operation and is currently unable to handle the incoming command)",
    5 => "Stack already started (no new configuration accepted)",
    128..=244 => "Failed (ZigBee event codes)",
    _ => "Unexpected status code"
};

        panic!(error);
    }

    String::from(format!(
        "sequence_number: {}, packet_type: {}, errors: {}",
        utils::pretty_hex(&[sequence_number]),
        utils::pretty_hex(&packet_type),
        utils::pretty_hex(&errors),
    ))
}

fn parse_version(payload: Vec<u8>) -> String {
    // if payload.len() != 5 {
    //     panic!("The payload should be 4 bytes long");
    // }

    // let major_version = [payload[0], payload[1]];
    //  TODO what is the excess byte at the end?

    format!(
        "installer version: {}.{}",
        utils::pretty_hex_u8(payload[2]),
        utils::pretty_hex_u8(payload[3]),
    )
}
