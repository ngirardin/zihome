import logging

import zigate

logging.basicConfig()
logging.root.setLevel(logging.DEBUG)

z = zigate.connect()

print('Got {} devices'.format(len(z.devices)))

for device in z.devices:
    # if (device.need_discovery()):
    #     print('!!!!! WARN Need to refresh the device')
    #     device.refresh_device()
    #     device.discover_device()

    print('Device addr: {}, name: {},  genericType: {}, get_type: {}, lqi: {}%, battery: {}%'.format(
        device.addr, device.name, device.genericType, device.get_type(), device.lqi_percent, round(device.battery_percent)))

    print('  - device.endpoints: {}'.format(device.endpoints))

    for endpoint in device.endpoints:
        print('    - {}'.format(endpoint))
        print(device.available_actions(endpoint))

    print('  - device.attributes: {}'.format(device.attributes))
    print('  - device.need_discovery: {}'.format(device.need_discovery()))
    for attribute in device.attributes:
        print('  - attribute:')
        # print('    - endpoint: {}'.format(attribute['endpoint']))
        # print('    - cluster: {}'.format(attribute['cluster']))
        # print('    - attribute: {}'.format(attribute['attribute']))
        # print('    - data: {}'.format(attribute['data']))
        # print('    - type: {}'.format(attribute'type']))
        print('    - name: {}'.format(attribute['name']))
        value = attribute['value']
        print('    - value: {}'.format(value))

#     # print(device.missing)
#     # print(device.discovery)
    # print(device.ieee)
#     # print('endpoints: ', device.get_endpoint())
    print('-----------------------------------------------------------------')
