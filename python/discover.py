import logging
import time

import zigate

logging.basicConfig()
logging.root.setLevel(logging.DEBUG)

z = zigate.connect()

# for device in z.devices:
#     print('------ discover_device {} -----')
#     z.discover_device(device, force=True)

z.discover_device('9c72', force=True)

while True:
    print('.', end='')
    time.sleep(0.5)
