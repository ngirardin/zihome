import logging
import time

import zigate

logging.basicConfig()
logging.root.setLevel(logging.DEBUG)

z = zigate.connect()

z.permit_join(duration=240)

while True:
    time.sleep(0.5)
    print('.', end='')


# 76e2
