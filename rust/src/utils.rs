pub fn pretty_hex(data: &[u8]) -> String {
    hex::encode(data)
        .chars()
        .collect::<Vec<char>>()
        .chunks(2)
        // .map(|c| format!("0x{}", c.iter().collect::<String>()))
        .map(|c| c.iter().collect::<String>())
        .collect::<Vec<String>>()
        .join(" ")
}

pub fn pretty_hex_u8(data: u8) -> String {
    hex::encode(&[data])
}

pub fn two_u8_to_u16(u8_1: u8, u8_2: u8) -> u16 {
    ((u8_1 as u16) << 8) | u8_2 as u16
}
