import logging
import time

import zigate

logging.basicConfig()
logging.root.setLevel(logging.DEBUG)

z = zigate.connect()

z.permit_join(duration=240)  # seconds

while True:
    time.sleep(0.5)

    if not z.is_permitting_join:
        print('permit_join timeout')
        exit

    print('.', end='')
