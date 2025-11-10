#########################################################################################################################
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#                               Update VIRUS database for PubPeer URLs and comments count                               #
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

# log file
logInfoFile='updatePubPeer.log'
if len(sys.argv)>3 :
    logInfoFile=sys.argv[3]
    logInfo("Logging to file "+logInfoFile,logInfoFile)

# Variables
start=1                                                                                 # First line to be treated
end=73212                                                                               # First line to be treated
titleUpdate=0                                                                           # Title update
pubDateUpdate=0                                                                         # Publication Date update
pubCitationUpdate=0                                                                     # Citations Count update
retDateUpdate=0                                                                         # Retraction Date update
pubPeerUpdate=1                                                                         # PubPeer URL and Comments Count update
apiTimeout=10                                                                           # API Call Timeout in second
apiRetries=10                                                                           # API Number of retries
apiRetryDelay=5                                                                         # API Delay between Retries

# Init
startTime = time.time()
httpHeadersCitations= {"authorization": "7f139668-0ae9-4987-be5b-eea33d61fb43"}
seleniumOptions = webdriver.ChromeOptions()
seleniumOptions.add_argument("headless")
seleniumDriver = webdriver.Chrome(options=seleniumOptions)
seleniumDriver = webdriver.Chrome()
records=[]
recordsCount=0


# Mysql connection
try:
    connection = mysql.connector.connect(
        host="192.168.1.3",
        user="virus",
        password="virus",
        database="virus"
    )
    cursor = connection.cursor()
    logInfo("Connection to MySQL DB successful",logInfoFile)
except mysql.connector.Error as err:
    logInfo(f"Error: {err}",logInfoFile)
conv = MySQLConverter()

# DIMCLI init
dimcli.login(key="B5850E7BB6F54405BDA4A24DE4CFE655", endpoint="https://app.dimensions.ai/api/dsl/v2")
dsl = dimcli.Dsl()

# Parsing JSON file
file_name = 'data.json'
parsed_records = []
for record in parseJSON(file_name):
    parsed_records.append(record)

todo=end-start+1
if parsed_records:
    n=0
    nn=0
    for r in parsed_records:
        n+=1

        # Data from JSON file
        if 'retraction_notice_doi' in r and 'retracted_pub_doi' in r and 'updates' in r and'type' in r['updates']:
            doi = r['retracted_pub_doi']
            
            if n>=start  and n<=end:

                # Following
                nn+=1
                currentTime=time.time()
                elapsed=currentTime-startTime
                eta=elapsed*todo/nn-elapsed
                etaFormatted=str(datetime.timedelta(seconds=eta)).split('.')[0]
                elapsedFormatted=str(datetime.timedelta(seconds=elapsed)).split('.')[0]        
                pc=100*nn/(todo)
                pc=f"{pc:.2f}"
                logInfotxt="Line "+str(nn)+"/"+str(end-start+1)+" ("+pc+"%) "+" Elapsed : "+str(elapsedFormatted)+" ETA : "+str(etaFormatted)+" Line : "+str(n)
                nbDash=len(logInfotxt)
                logInfo("        "+"-"*nbDash,logInfoFile)
                logInfo("        "+logInfotxt,logInfoFile)
                logInfo("        "+"-"*nbDash,logInfoFile)

                pubpeerUrl = 'https://pubpeer.com/search?q='+urllib.parse.quote(doi)
                seleniumDriver.get(pubpeerUrl)
                WebDriverWait(seleniumDriver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, "div.footer")))
                time.sleep(1)
                pubpeerHtml = seleniumDriver.page_source
                pubpeerSoup = BeautifulSoup(pubpeerHtml, 'html.parser')

                # Comments count
                pubpeerComments=pubpeerSoup.find('div',class_='panel-footer')
                pubpeerCommentcount=0
                if pubpeerComments:
                    pubpeerCommentsearch=str(pubpeerComments).replace('fa-comment-o','')
                    pos=str(pubpeerCommentsearch).find('comment')
                    char=str(pubpeerCommentsearch)[pos-1:pos]
                    pubpeerCommentcountText=''
                    while char!='>' :
                        pubpeerCommentcountText=char+pubpeerCommentcountText
                        pos=pos-1
                        char=str(pubpeerCommentsearch)[pos-1:pos]
                    if not str.isnumeric(pubpeerCommentcountText) :
                        pubpeerCommentcountText=''
                    if pubpeerCommentcountText!='':
                        pubpeerCommentcount=int(pubpeerCommentcountText)
                        logInfo("      Pubpeer : "+str(pubpeerCommentcount)+" comments found",logInfoFile)
                        sqlPubpeerCommentcount="UPDATE `article` set pubpeerCommentcount='%s' where doi='https://doi.org/%s'" % (pubpeerCommentcount, doi)
                        cursor.execute(sqlPubpeerCommentcount)
                        connection.commit()

                # Comment URL
                pubpeerResults=pubpeerSoup.find('div',class_='panel-pubpeer')
                pubpeerLink=''
                if pubpeerResults:
                    pubpeerH3=pubpeerResults.find('h3')
                    if pubpeerH3 :
                        pubpeerA=pubpeerH3.find('a')
                        if pubpeerA and pubpeerA.has_attr('href'):
                            pubpeerLink='https://pubpeer.com'+pubpeerA['href']
                            sqlPubpeer="UPDATE `article` set pubpeer='%s' where doi='https://doi.org/%s'" % (pubpeerLink, doi)
                            cursor.execute(sqlPubpeer)
                            connection.commit()
                            logInfo("      Pubpeer : "+pubpeerLink,logInfoFile)
                seleniumDriver.back()

# This is the end, my only friend, the end
logInfo('Finished in '+str(elapsedFormatted),logInfoFile)
cursor.close()
connection.close()
seleniumDriver.close()
