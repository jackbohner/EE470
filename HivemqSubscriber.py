import os

import paho.mqtt.client as mqtt
import mysql.connector
from dotenv import load_dotenv


# Load private database settings from .env
load_dotenv()


# MQTT settings
BROKER_URL = "broker.hivemq.com"
BROKER_PORT = 1883
TOPIC = "testtopic/temp/outTopic/jackbohner_470"


# Database settings from .env
DB_HOST = os.getenv("DB_HOST")
DB_NAME = os.getenv("DB_NAME")
DB_USER = os.getenv("DB_USER")
DB_PASSWORD = os.getenv("DB_PASSWORD")


def push_value_to_database(sensor_value):
    connection = None
    cursor = None

    try:
        connection = mysql.connector.connect(
            host=DB_HOST,
            port=3306,
            database=DB_NAME,
            user=DB_USER,
            password=DB_PASSWORD
        )

        cursor = connection.cursor()

        sql = """
            INSERT INTO mqtt_potentiometer (value)
            VALUES (%s)
        """

        cursor.execute(sql, (sensor_value,))
        connection.commit()

        print("Inserted into database:", sensor_value)

    except mysql.connector.Error as error:
        print("Database error:", error)

    finally:
        if cursor is not None:
            cursor.close()

        if connection is not None and connection.is_connected():
            connection.close()


def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("Connected to HiveMQ broker")

        client.subscribe(TOPIC)

        print("Subscribed to:")
        print(TOPIC)

    else:
        print("MQTT connection failed")
        print("Return code:", rc)


def on_message(client, userdata, message):
    received_value = message.payload.decode().strip()

    print("Received MQTT value:", received_value)
    print("Received from topic:", message.topic)

    try:
        sensor_value = float(received_value)
        push_value_to_database(sensor_value)

    except ValueError:
        print("Ignored non-numeric message:", received_value)


client = mqtt.Client()

client.on_connect = on_connect
client.on_message = on_message

print("Connecting to HiveMQ...")

client.connect(
    BROKER_URL,
    BROKER_PORT,
    60
)

client.loop_forever()