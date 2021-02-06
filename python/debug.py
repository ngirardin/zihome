import array
import logging
import struct
import time

import serial
import zigate

# logging.basicConfig()
# logging.root.setLevel(logging.DEBUG)

# z = zigate.connect()

ser = serial.Serial(port='/dev/ttyUSB0', baudrate=115200)
ser.close()
ser.open()
print("Send")
data = bytes([0x01, 0x02, 0x10, 0x18, 0x02,
              0x10, 0x02, 0x11, 0x18, 2, 0x11, 3])
print(data)
ser.write(data)


# from lib
# b'\x01\x02\x10\x18\x02\x10\x02\x11\x18\x02\x11\x03'
# b'\x01\x02\x10\x10\x02\x10\x02\x10\x10\x03'
# from here
# b'\x01\x02\x10\x18\x02\x10\x02\x11\x12\x02\x0b\x03'

while True:
    print("waiting for {} bytes".format(ser.in_waiting))
    read_val = ser.read(size=1024)
    print(read_val)
    time.sleep(0.05)
