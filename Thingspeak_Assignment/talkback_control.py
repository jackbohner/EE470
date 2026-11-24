import os
import time
import requests
import RPi.GPIO as GPIO
from dotenv import load_dotenv


load_dotenv()

TALKBACK_ID = os.getenv("TALKBACK_ID")
TALKBACK_KEY = os.getenv("TALKBACK_KEY")

LED_PIN = 17

GPIO.setmode(GPIO.BCM)
GPIO.setup(LED_PIN, GPIO.OUT)
GPIO.output(LED_PIN, GPIO.LOW)


def get_command():
    url = (
        f"https://api.thingspeak.com/talkbacks/"
        f"{TALKBACK_ID}/commands/execute"
    )

    response = requests.get(
        url,
        params={"api_key": TALKBACK_KEY},
        timeout=10
    )

    return response.text.strip().upper()


try:
    while True:
        command = get_command()

        print(f"Received command: {command}")

        if command == "ON":
            GPIO.output(LED_PIN, GPIO.HIGH)
            print("LED turned ON")

        elif command == "OFF":
            GPIO.output(LED_PIN, GPIO.LOW)
            print("LED turned OFF")

        elif command in ("", "0"):
            print("No command in queue")

        else:
            print("Unknown command")

        time.sleep(30)

except KeyboardInterrupt:
    print("Program stopped.")

finally:
    GPIO.output(LED_PIN, GPIO.LOW)
    GPIO.cleanup()