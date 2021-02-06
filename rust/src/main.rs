use serialport::SerialPort;
use std::io::{self, Write};
use std::time::Duration;

mod commands;
mod frame;
mod responses;
mod utils;

const PORT: &str = "/dev/ttyUSB0";

fn open_port() -> Box<dyn SerialPort> {
    serialport::new(PORT, 115_200)
        .timeout(Duration::from_millis(10))
        .open()
        .expect("Failed to open port")
}

fn main() {
    let mut port = open_port();

    let cmd = commands::get_version();
    let vec: Vec<u8> = frame::encode(cmd, vec![]);

    let str = utils::pretty_hex(&vec[..]);

    let arr: &[u8] = &vec.clone();
    println!("Sending {}", str);

    port.write_all(arr).expect("Failed to write to serial port");

    let mut buffer: Vec<u8> = vec![0; 32];

    let mut acc: Vec<u8> = Vec::new();

    loop {
        match port.read(&mut buffer) {
            Ok(_len) => {
                let keep = strip_trailing_zeros(buffer.clone());
                acc.extend(keep);

                let position_opt = acc.iter().position(|b| b == &0x03);

                println!("acc is {}", utils::pretty_hex(&acc));

                if position_opt.is_some() {
                    let position = position_opt.unwrap() + 1;

                    acc.rotate_left(position);
                    let to_parse = acc.split_off(acc.len() - position);

                    println!("main: raw frame: {}", utils::pretty_hex(&to_parse));

                    let response = frame::decode(to_parse);

                    // RESPONSE 0x8000 - Status response : status:0, sequence:0, packet_type:16, error:b'\x00\x00', lqi:0
                    // RESPONSE 0x8010 - Version list : major:3, installer:31d, lqi:0, version:3.1d
                    // STATUS code to command 0x0010:RESPONSE 0x8000 - Status response : status:0, sequence:0, packet_type:16, error:b'\x00\x00', lqi:0

                    println!("main: {}", responses::parse(response));

                    println!("acc is still {}", utils::pretty_hex(&acc));
                }
            }
            Err(ref e) if e.kind() == io::ErrorKind::TimedOut => (),
            Err(e) => eprintln!("{:?}", e),
        }
    }
}

fn strip_trailing_zeros(buffer: Vec<u8>) -> Vec<u8> {
    let mut bytes: Vec<u8> = buffer.clone();
    bytes.reverse();
    let reverse_position = bytes.iter().position(|&b| b != 0).unwrap();
    let position = bytes.len() - reverse_position;

    let slice = &buffer[..position];
    slice.to_vec()
}
