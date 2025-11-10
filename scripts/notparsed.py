#########################################################################################################################
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#                     Check unparsed DOIs from the JSON BigQuery result file in the database                            #
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#########################################################################################################################

# Imports
import json, mysql.connector, dimcli, time, datetime, urllib, requests, sys
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from mysql.connector.conversion import MySQLConverter
from bs4 import BeautifulSoup
from commons import parseJSON, logInfo, emptyNull

# Mysql Connection
connection = mysql.connector.connect(
    host="192.168.1.3",
    user="virus",
    password="virus",
    database="virus"
)
cursor = connection.cursor()

# Execution
file_name = 'data.json'
parsed_records = []
records=[]
nb=0
for record in parseJSON(file_name):
    parsed_records.append(record)
for r in parsed_records:
    if 'retracted_pub_doi' in r:
        doi = r['retracted_pub_doi']
        if not doi in records :
            records.append(doi)
for doi in records:
    sqlExists="SELECT * FROM `article` WHERE `doi`='https://doi.org/%s'" % (doi)
    cursor.execute(sqlExists)
    exists = cursor.fetchall()
    doExist = len(exists)
    if doExist==0:
        nb+=1
        print(doi)
print(str(nb)+" articles found that are not in the database")