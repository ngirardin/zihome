import inspect
import json
import logging
import time

import psycopg2
import zigate
from zigate import dispatcher

# CREATE TABLE events(id SERIAL PRIMARY KEY, date TIMESTAMP DEFAULT NOW(), name TEXT NOT NULL, payload TEXT NOT NULL);
# CREATE TABLE points(id SERIAL PRIMARY KEY, device TEXT NOT NULL, name TEXT NOT NULL, value TEXT NOT NULL);

device_dimmer = '242e'
device_switch = 'a7dc'
# device_alarm = c773


def insert_event(name, payload):
    sql = "INSERT INTO events(name, payload) VALUES('{}', '{}')".format(
        name, payload)
    print(sql)
    cur.execute(sql)
    conn.commit()


def insert_point(device, name, value):
    sql = "INSERT INTO points(device, name, value) VALUES('{}', '{}', '{}')".format(
        device, name, value)
    print(sql)
    cur.execute(sql)
    conn.commit()


def dimmer_on():
    device = z.get_device_from_addr(device_dimmer)
    z.action_onoff(device, endpoint=1, onoff=zigate.ON)


def dimmer_off():
    device = z.get_device_from_addr(device_dimmer)
    z.action_onoff(device, endpoint=1, onoff=zigate.OFF)


def my_callback(sender, signal, **kwargs):
    device = kwargs['device']

    if signal != zigate.ZIGATE_ATTRIBUTE_UPDATED:
        insert_event(signal, device)
        return

    # Attribute updated
    attribute = kwargs['attribute']

    addr = device.addr

    name = attribute['name']
    value = attribute['value']

    # Insert points
    insert_point(addr, 'lqi', device.lqi_percent)

    if name == 'xiaomi' or name == 'current_delivered':
        # xiaomi specific properties on the temp sensors
        insert_point(addr, name, json.dumps(value))
        return

    if isinstance(value, dict) and 'alarm1' in value:
        # detected an alarm
        alarm = value['alarm1']
        insert_point(addr, 'alarm', alarm)

        if alarm:
            print('Presence sensor ON')
            dimmer_on()
        else:
            print('Presence sensor OFF')
            dimmer_off()

        return

    insert_point(addr, name, value)


conn = psycopg2.connect("host=localhost user=postgres password=pwd")
cur = conn.cursor()

logging.basicConfig()
# logging.root.setLevel(logging.DEBUG)

dispatcher.connect(my_callback, zigate.ZIGATE_DEVICE_ADDED)
# dispatcher.connect(my_callback, zigate.ZIGATE_DEVICE_UPDATED)
dispatcher.connect(my_callback, zigate.ZIGATE_DEVICE_REMOVED)
dispatcher.connect(my_callback, zigate.ZIGATE_DEVICE_ADDRESS_CHANGED)
# dispatcher.connect(my_callback, zigate.ZIGATE_ATTRIBUTE_ADDED)
dispatcher.connect(my_callback, zigate.ZIGATE_ATTRIBUTE_UPDATED)
dispatcher.connect(my_callback, zigate.ZIGATE_DEVICE_NEED_DISCOVERY)

z = zigate.connect()
# z.get_device_from_addr('76da').action_onoff(zigate.ON)

while True:
    time.sleep(0.5)
    print('.', end='')
