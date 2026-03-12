import pymysql

# Database connection parameters
DB_NAME = 'hopephillips_foundation_data'
DB_USER = 'techa'
DB_PASSWORD = 'Techa.Tech500'
DB_HOST = 'localhost'
DB_PORT = 3306

def get_db_connection(db_name=DB_NAME):
    conn =pymysql.connect(
        database=db_name,
        user=DB_USER,
        password=DB_PASSWORD,
        host=DB_HOST,
        port=DB_PORT
    )
    
    return conn

try:
    # Connect to the default 'mysql' database to check for the existence of the target database
    conn = get_db_connection(db_name='mysql')
    conn.autocommit = True
    cursor = conn.cursor()

    # Check if the target database exists
    cursor.execute(f"SELECT 1 FROM information_schema.schemata WHERE schema_name = '{DB_NAME}'")
    exists = cursor.fetchone()
    if not exists:
        cursor.execute(f'CREATE DATABASE {DB_NAME}')
        print(f"Database {DB_NAME} created successfully.")
    else:
        print(f"Database {DB_NAME} already exists.")

    cursor.close()
    conn.close()

    # # Connect to the new database to create the table and insert data
    # conn = get_db_connection()
    # cursor = conn.cursor()

    # # Create the books table if it doesn't exist
    # cursor.execute('''
    #     CREATE TABLE IF NOT EXISTS account (
    #         id SERIAL PRIMARY KEY,
    #         name VARCHAR(100),
    #         username VARCHAR(100),
    #         email VARCHAR(100), 
    #         password VARCHAR(100),
    #         phone VARCHAR(100),
    #         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    #     )
    # ''')

    # # Commit the transaction
    # conn.commit()

except Exception as e:
    print(f"MySQL connection error: {e}")

