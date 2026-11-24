import os
import smtplib
from dotenv import load_dotenv
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart


# Load values from the .env file
load_dotenv()

GMAIL_USER = os.getenv("GMAIL_USER")
GMAIL_APP_PASSWORD = os.getenv("GMAIL_APP_PASSWORD")
RECIPIENT_EMAIL = os.getenv("RECIPIENT_EMAIL")


def get_cpu_temperature():
    with open("/sys/class/thermal/thermal_zone0/temp", "r") as file:
        temperature = int(file.read()) / 1000

    return temperature


# Check that the email settings were loaded
if not GMAIL_USER or not GMAIL_APP_PASSWORD or not RECIPIENT_EMAIL:
    print("Email settings are missing from the .env file.")
    exit()


temperature = get_cpu_temperature()

print(f"CPU temperature: {temperature:.2f} C")


if temperature > 40:
    message = MIMEMultipart()

    message["From"] = GMAIL_USER
    message["To"] = RECIPIENT_EMAIL
    message["Subject"] = "Raspberry Pi CPU Temperature Alert"

    body = (
        "The Raspberry Pi CPU temperature is above 40 degrees Celsius.\n\n"
        f"Current temperature: {temperature:.2f} C"
    )

    message.attach(MIMEText(body, "plain"))

    try:
        with smtplib.SMTP("smtp.gmail.com", 587) as server:
            server.ehlo()
            server.starttls()
            server.ehlo()
            server.login(GMAIL_USER, GMAIL_APP_PASSWORD)

            server.sendmail(
                GMAIL_USER,
                RECIPIENT_EMAIL,
                message.as_string()
            )

        print("Email sent successfully.")

    except Exception as error:
        print(f"Email failed: {error}")

else:
    print("Temperature is below the 40 C threshold.")