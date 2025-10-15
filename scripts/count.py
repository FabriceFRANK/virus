#########################################################################################################################
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#                                   Count unique DOIs in the JSON BigQuery result file                                  #
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#########################################################################################################################

# Imports
import mysql.connector, dimcli, time, datetime, urllib, requests, sys
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from mysql.connector.conversion import MySQLConverter
from bs4 import BeautifulSoup
from commons import parseJSON

file_name = 'data.json'
parsed_records = []
records=[]
for record in parseJSON(file_name):
    parsed_records.append(record)
for r in parsed_records:
    if 'retracted_pub_doi' in r:
        doi = r['retracted_pub_doi']
        if not doi in records :
            records.append(doi)
print(len(records))
