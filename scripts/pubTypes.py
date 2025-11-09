#########################################################################################################################
#                                                                                                                       #
#                                                                                                                       #
#          Search for types other thant 'Expression of cocnern' and 'Retraction in the JSON file                        #
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

# Parsing JSON file
file_name = 'data.json'
parsed_records = []
for record in parseJSON(file_name):
    parsed_records.append(record)

# Action
logInfoFile='types;Log'
logInfo("Start",logInfoFile)
if parsed_records:
    for r in parsed_records:
        if 'retracted_pub_doi' in r :
            doi = r['retracted_pub_doi']
            if  'updates' in r and'type' in r['updates']:
                type = r["updates"]['type']
                if type!="Retraction notice" and type!="Expression of concern":
                    logInfo("    Type '"+type+"'",logInfoFile)
            else :
                logInfo("    No updates for doi "+doi,logInfoFile)
        else :
            print(r)
logInfo("End",logInfoFile)
