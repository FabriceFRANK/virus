#########################################################################################################################
#                                                                                                                       #
#               Update VIRUS database from BigQuery JSON resultas and Dimension.ai database                             #
#                                                                                                                       #
#                                                                                                                       #
#                   Syntax :                                                                                            #
#                       pythoN3 updateDB.py [start] [end] [logFile] [force]                                             #
#                                                                                                                       #
#                                   start (int) : First line of the JSON file to parse                                  #
#                                   end (int) : Last line of the JSON file to parse                                     #
#                                   logFile (str) : File to log advancement to                                          #
#                                   force (0/1) : 0 : Update only. 1 : Update entries that already exist                #
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

# Init
startTime = time.time()                                                                             # Start time to measure elapsed time and ETA
start=1                                                                                             # Default first line to parse
end=len(parsed_records)                                                                             # Default last line to parse
logInfoFile='updateDb.log'                                                                          # Default log file
force=0                                                                                             # By default update only
recordsRet=[]
recordsEoc=[]
recordsCount=0
n=0

# Init Selenium
seleniumOptions = webdriver.ChromeOptions()
seleniumOptions.add_argument("headless")
seleniumDriver = webdriver.Chrome(options=seleniumOptions)
seleniumDriver = webdriver.Chrome()

# Parameters
if len(sys.argv)>3 :
    logInfoFile=sys.argv[3]
if len(sys.argv)>1 :
    start=int(sys.argv[1])
if len(sys.argv)>2 :
    end=int(sys.argv[2])
if len(sys.argv)>4 :
    force=int(sys.argv[4])
logInfo(f"Parsed {len(parsed_records)} records in "+file_name+" file.",logInfoFile)
logInfo("Logging to file "+logInfoFile,logInfoFile)
logInfo("Start on line "+str(start),logInfoFile)
logInfo("Until line "+str(end),logInfoFile)
logInfo("Force update "+str(force),logInfoFile)
todo=end-start+1
done=0

# Mysql connection
try:
    connection = mysql.connector.connect(
        host="192.168.1.3",
        user="root",
        password="Mcsuapte@2017",
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

# Go
logInfo("Start on line "+str(start),logInfoFile)
if parsed_records:
    for r in parsed_records:
        n+=1

        if n>=start and n<=end :
            done+=1
            # Data from JSON file
            if  'retracted_pub_doi' in r and 'updates' in r and'type' in r['updates']:
                type = r["updates"]['type']
                doi = r['retracted_pub_doi']
                go=1
                logInfo("Line "+str(n)+" - doi "+doi+" - Type "+type,logInfoFile)
                if type=="Retraction notice":
                    if doi in recordsRet:
                        go=0
                    else :
                        recordsRet.append(doi)
                if type=="Expression of concern":
                    if doi in recordsRet:
                        go=0
                    else :
                        recordsEoc.append(doi)
                if go==0 :
                    logInfo("    Already parsed",logInfoFile)
                else :
                    table=''
                    reason=''
                    source=''
                    retraction_doi=''
                    retDate=''
                    if 'retraction_notice_doi' in r :
                        retraction_doi = r["retraction_notice_doi"]
                    if type=="Retraction notice":
                        table='retraction'
                    if type=="Expression of concern":
                        table='eoc'
                    if 'readon' in r['updates'] :
                        reason = r["updates"]['reasons']
                    if 'source' in r['updates'] :
                        source = r["updates"]['source']
                    if 'date' in r["updates"] :
                        retDate = r["updates"]['date']
                    if table=='':
                        logInfo('    Type unknown '+type)
                    else :
                        queryExists="SELECT * FROM `article` a INNER JOIN "+table+" r on r.doi=a.doi where a.doi='https://doi.org/%s'" % (doi)
                        cursor.execute(queryExists)
                        dataExists = cursor.fetchall()
                        articleExists = len(dataExists)
                        if articleExists==0 or force==1:
                            if articleExists==0 :
                                logInfo("    New article",logInfoFile)
                            else :
                                logInfo("    Update forced",logInfoFile)
                            
                            # Init Selenium                            
                            pubpeerUrl = 'https://pubpeer.com/search?q='+urllib.parse.quote(doi)
                            seleniumDriver.get(pubpeerUrl)
                            logInfo("    Preloading PubPeer",logInfoFile)

                            # Data from Dimension   
                            logInfo("    Query Dimensions.ai",logInfoFile)  
                            queryDimension= f"""search publications where doi="{doi}" return publications [title+doi+year+type+authors+journal+times_cited+date+altmetric]""" 
                            dataDimension = dsl.query(queryDimension)                            
                            pubDate=''
                            citationsCount=''
                            altmetrics=''
                            title="Unknown"
                            journal="Unknown"
                            journalIdDimension="See database"

                            # Parse doi data
                            # Title
                            if 'title' in dataDimension['publications'][0]:
                                title=dataDimension['publications'][0]['title']
                            logInfo("      Title : "+title,logInfoFile)

                            # Journal
                            if 'journal' in dataDimension['publications'][0]:
                                if 'journal' in dataDimension['publications'][0] and 'title' in dataDimension['publications'][0]['journal']:
                                    journal=dataDimension['publications'][0]['journal']['title']
                                    journalIdDimension=dataDimension['publications'][0]['journal']['id']
                            sqlExists = "select * from journal where name='%s'" % (conv.escape(journal))
                            cursor.execute(sqlExists)
                            dataExists = cursor.fetchall()
                            exists = len(dataExists)
                            if exists==0:
                                sqlInsert="INSERT INTO `journal` (`name`, `dimensionId`) VALUES ('%s','%s')" % (conv.escape(journal),journalIdDimension)
                                cursor.execute(sqlInsert)
                                connection.commit()
                                journalId=cursor.lastrowid
                            else :
                                journalId=dataExists[0][0]    
                            logInfo("      Journal : "+journal+" ("+str(journalId)+")",logInfoFile)
                                
                            # Publication date
                            if 'date' in dataDimension['publications'][0]:
                                pubDate=dataDimension['publications'][0]['date']
                                logInfo("      Publication date : "+str(pubDate),logInfoFile)

                            # Citations
                            if 'times_cited' in dataDimension['publications'][0]:
                                citationsCount=dataDimension['publications'][0]['times_cited']
                                logInfo("      Citations : "+str(citationsCount),logInfoFile)

                            # Altmetrics
                            if 'altmetric' in dataDimension['publications'][0]:
                                altmetrics=dataDimension['publications'][0]['altmetric']
                                logInfo("      Altmetrics : "+str(altmetrics),logInfoFile)
                            
                            # Authors
                            if 'authors' in dataDimension['publications'][0]:
                                authors=dataDimension['publications'][0]['authors']
                            else :
                                authors=[]     
                                    
                            # PubPeer
                            WebDriverWait(seleniumDriver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR, "div.footer")))
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

                            # Insert into database
                            queryExists="SELECT * FROM `article` where doi='https://doi.org/%s'" % (doi)
                            cursor.execute(queryExists)
                            doiExists = cursor.fetchall()
                            doiInDB = len(doiExists)
                            if doiInDB==0 :
                                queryInsert="INSERT INTO `article` (`doi`, `title`, `pubDate`, `citation`, `altmetrics`, `idJournal`, `pubpeer`, `pubpeerCommentcount`) VALUES ('https://doi.org/%s', '%s', '%s', %s, %s, %s, '%s', %s)" % (doi, conv.escape(title), emptyNull(pubDate), emptyNull(citationsCount), emptyNull(altmetrics), journalId, pubpeerLink, pubpeerCommentcount)
                                cursor.execute(queryInsert)
                                connection.commit()
                                logInfo("      Inserted into database",logInfoFile)
                            else :
                                queryUpdate="UPDATE `article` SET `title`='%s', `pubDate`='%s', `citation`=%s, `altmetrics`=%s, `idJournal`=%s, `pubpeer`='%s', `pubpeerCommentcount`=%s WHERE `doi`='https://doi.org/%s'" % (conv.escape(title), emptyNull(pubDate), emptyNull(citationsCount), emptyNull(altmetrics), journalId, pubpeerLink, pubpeerCommentcount, doi)
                                cursor.execute(queryUpdate)
                                connection.commit()
                                logInfo("      Article updated in database",logInfoFile)

                                    
                            #Authors
                            sqlDelete="DELETE FROM `articleAuthor` WHERE `doiArticle`='https://doi.org/%s'" % (doi)
                            cursor.execute(sqlDelete)
                            connection.commit()
                            if len(authors)>0 :
                                logInfo("      Authors : "+str(len(authors)),logInfoFile)
                                for a in authors :
                                    firstName=a['first_name']
                                    lastName=a['last_name']
                                    dimensionAuthorId=''
                                    if 'researcher_id' in a :
                                        dimensionAuthorId=a['researcher_id']
                                    if a['orcid']:
                                        orcid=a['orcid'][0]
                                        sqlExists = "SELECT * FROM `author` WHERE `firstname`='%s' AND `name`='%s' AND `orcid`='%s'" % (conv.escape(firstName), conv.escape(lastName), orcid)
                                        cursor.execute(sqlExists)
                                    else :
                                        sqlExists = "SELECT * FROM `author` WHERE `firstname`='%s' AND `name`='%s'" % (conv.escape(firstName), conv.escape(lastName))
                                        cursor.execute(sqlExists)
                                    dataExists = cursor.fetchall()
                                    exists = len(dataExists)
                                    if exists==0:
                                        if a['orcid']:
                                            sqlInsert = "INSERT INTO `author` (`firstname`,`name`, `orcid`, `dimensionId`) VALUES ('%s', '%s', '%s', '%s')" % (conv.escape(firstName), conv.escape(lastName), orcid,dimensionAuthorId)
                                        else :
                                            sqlInsert = "INSERT INTO `author` (`firstname`,`name`, `dimensionId`) VALUES ('%s', '%s', '%s')" % (conv.escape(firstName), conv.escape(lastName),dimensionAuthorId)
                                        cursor.execute(sqlInsert)
                                        connection.commit()
                                        authorId=cursor.lastrowid
                                    else :
                                        authorId=dataExists[0][0]
                                    sqlArticleAuthor="INSERT INTO `articleAuthor` (`doiArticle`, `idAuthor`) VALUES ('https://doi.org/%s','%s')"  % (doi, authorId)
                                    cursor.execute(sqlArticleAuthor)
                                    connection.commit()
                                    logInfo("          Author : "+firstName+" "+lastName,logInfoFile)
                            else :
                                logInfo("      Unknown authors",logInfoFile)

                            # Retraction / EoC
                            if table!='':
                                sqlExists="SELECT * FROM `%s` WHERE `doi`='https://doi.org/%s'" % (table, doi)
                                cursor.execute(sqlExists)
                                dataExists = cursor.fetchall()
                                exists = len(dataExists)
                                if exists==0:
                                    if reason!='' and reason!=[] and reason[0]:
                                        reasonRetraction=reason[0]
                                    else :
                                        reasonRetraction=''
                                    sqlRetraction="INSERT INTO `%s` (`doi`, `source`, `reason`, `%s`,`%sDate`) VALUES ('https://doi.org/%s', '%s', '%s', 'https://doi.org/%s', '%s')" % (table, table, table, doi, conv.escape(source), conv.escape(reasonRetraction), retraction_doi, retDate)
                                    cursor.execute(sqlRetraction)
                                    connection.commit()
                                else :
                                    sqlRetDate="UPDATE `%s` set `%sDate`='%s' where `doi`='https://doi.org/%s'" % (table, table, retDate, doi)
                                    cursor.execute(sqlRetDate)
                                    connection.commit()
                                logInfo("      "+type+" : "+retraction_doi,logInfoFile)
                        else :
                            logInfo("    Article already in the database",logInfoFile)
            
                # Advancement info
                pc=100*done/todo
                formattedPc = f"{pc:.2f}"
                currentTime=time.time()
                elapsed=currentTime-startTime
                eta=elapsed*(todo-done)/done
                etaFormatted=str(datetime.timedelta(seconds=eta)).split('.')[0]
                elapsedFormatted=str(datetime.timedelta(seconds=elapsed)).split('.')[0]
                logInfotxt="-------- "+str(done)+"/"+str(todo)+" ("+formattedPc+"%) Elapsed "+elapsedFormatted+" ETA : "+etaFormatted+" --------"
                nbDash=len(logInfotxt)
                logInfo("                "+"-"*nbDash,logInfoFile)
                logInfo("                "+logInfotxt,logInfoFile)
                logInfo("                "+"-"*nbDash,logInfoFile)

# This is the end, my only friend, the end
currentTime=time.time()
elapsed=currentTime-startTime
elapsedFormatted=str(datetime.timedelta(seconds=elapsed)).split('.')[0]
logInfo("Task terminated in "+elapsedFormatted,logInfoFile)
seleniumDriver.close()
cursor.close()
connection.close()
