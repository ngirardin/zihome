import logging

import zigate

logging.basicConfig()
# logging.root.setLevel(logging.DEBUG)

z = zigate.connect(channel=11)

print('Got {} devices'.format(len(z.devices)))

for device in z.devices:
    if (device.need_discovery()):
        print('!!!!! WARN Need to refresh the device device {}'.format(device))
        device.refresh_device()
        device.discover_device()

    print('Device addr: {}, name: {},  genericType: {}, get_type: {}, lqi: {}%, battery: {}%'.format(
        device.addr, device.name, device.genericType, device.get_type(), device.lqi_percent, round(device.battery_percent)))

    # print('  - device.endpoints: {}'.format(device.endpoints))

    for endpoint in device.endpoints:
        print('    - {}'.format(endpoint))
        print(device.available_actions(endpoint))

    # print('  - device.attributes: {}'.format(device.attributes))
    print('  - device.need_discovery: {}'.format(device.need_discovery()))

    print('  - attributes:')
    for attribute in device.attributes:
        # print('    - endpoint: {}'.format(attribute['endpoint']))
        # print('    - cluster: {}'.format(attribute['cluster']))
        # print('    - attribute: {}'.format(attribute['attribute']))
        attribute_name = attribute['name'] if 'name' in attribute else '[No name]'
        attribute_value = attribute['value'] if 'value' in attribute else '[No value]'
        attribute_type = attribute['type'] if 'type' in attribute else '[No type]'
        attribute_data = attribute['data'] if 'data' in attribute else '[No data]'

        print('    - endpoint: {}, cluster: {}, attribute: {}, name: {}, data: {}, type: {}, value: {}'.format(attribute['endpoint'], attribute['cluster'], attribute['attribute'], attribute_name, attribute_data, attribute_type, attribute_value))

#     # print(device.missing)
#     # print(device.discovery)
    # print(device.ieee)
#     # print('endpoints: ', device.get_endpoint())
    print('-----------------------------------------------------------------')
