import psycopg2
from psycopg2 import sql

# Database connection parameters
DB_NAME = 'hopephillips_foundation_data'
DB_USER = 'edet'
DB_PASSWORD = 'edet'
DB_HOST = 'localhost'
DB_PORT = '5432'

def get_db_connection(db_name=DB_NAME):
    
    conn = psycopg2.connect(
        dbname=db_name,
        user=DB_USER,
        password=DB_PASSWORD,
        host=DB_HOST,
        port=DB_PORT
    )
    
    return conn

try:
    # Connect to the default 'postgres' database
    conn = get_db_connection(db_name='postgres')
    conn.autocommit = True
    cursor = conn.cursor()

    # Check if the target database exists
    cursor.execute(
        "SELECT 1 FROM pg_database WHERE datname = %s",
        (DB_NAME,)
    )
    exists = cursor.fetchone()
    if not exists:
        cursor.execute(sql.SQL("CREATE DATABASE {}").format(
            sql.Identifier(DB_NAME)
        ))
        print(f"Database {DB_NAME} created successfully.")
    else:
        print(f"Database {DB_NAME} already exists.")

    cursor.close()
    conn.close()

    # # Connect to the new database
    # conn = get_db_connection()
    
    # with conn.cursor() as cursor:
    #     cursor.execute('''
    #         CREATE TABLE IF NOT EXISTS account (
    #             id SERIAL PRIMARY KEY,
    #             name VARCHAR(100),
    #             username VARCHAR(100),
    #             email VARCHAR(100), 
    #             password VARCHAR(100),
    #             phone VARCHAR(100),
    #             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    #         )
    #     ''')

    # conn.commit()
    # print("Table 'account' created successfully.")

except Exception as e:
    print(f"PostgreSQL connection error: {e}")
finally:
    if 'conn' in locals() and conn is not None:
        conn.close()