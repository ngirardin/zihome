import logging
import time

import zigate

logging.basicConfig()
# logging.root.setLevel(logging.DEBUG)

z = zigate.connect()

dimmer = '8625'
device = z.get_device_from_addr(dimmer)


# z.discover_device(device, force=True)
# z.refresh_device(device, force=True)

toggle = 2

while True:
    print('Toggle')
    z.action_onoff(dimmer, endpoint=1, onoff=toggle)
    time.sleep(5)
    print('to on, rate 1')
    z.action_move_level_onoff(dimmer, endpoint=1, onoff=toggle, level=50)  # up
    time.sleep(10)
    print('to off, rate 2')
    z.action_move_level_onoff(
        dimmer, endpoint=1, onoff=toggle, level=100)  # up
    time.sleep(10)
