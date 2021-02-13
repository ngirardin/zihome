import atexit
import logging
import time

import zigate


def on_exit(z):
    print('Closing the zigate...')
    z.close()
    print('Closed!')


logging.basicConfig()
# logging.root.setLevel(logging.DEBUG)

# channels = channels or [11, 14, 15, 19, 20, 24, 25, 26]

channel = 11

print("Using channel {}".format(channel))

z = zigate.connect(channel=channel)

atexit.register(on_exit, z)

device_dimmer = '242e'
device_switch = 'a7dc'

while True:
    # print('switch on')
    # z.action_onoff(device_switch, endpoint=1, onoff=zigate.ON)

    print('dimmer on')
    z.action_move_level_onoff(
        device_dimmer, endpoint=1, onoff=zigate.ON, level=30)

    time.sleep(3)

    # print('switch off')
    # z.action_onoff(device_switch, endpoint=1, onoff=zigate.OFF)

    print('dimmer off')
    print('lqi: {}, lqi_percent: {}'.format(z.get_device_from_addr(
        device_dimmer).lqi, z.get_device_from_addr(device_dimmer).lqi_percent))
    z.action_onoff(device_dimmer, endpoint=1, onoff=zigate.OFF)
    time.sleep(3)
