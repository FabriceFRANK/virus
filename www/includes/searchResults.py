# Imports
import dimcli, sys, os, mysql.connector
from datetime import datetime, date

# Mysql connection
connection = mysql.connector.connect(
    host="192.168.1.3",
    user="virus",
    password="virus",
    database="virus"
)
cursor = connection.cursor()

# Get doi
#doi='https://doi.org/10.1186/s41073-023-00134-4'         # For test purposes
if len(sys.argv)>1 or doi :
    if len(sys.argv)>1 :
        doi=sys.argv[1]

    # DIMCLI init
    doi=doi.replace('https://doi.org/','')
    dimcli.login(key="B5850E7BB6F54405BDA4A24DE4CFE655", endpoint="https://app.dimensions.ai/api/dsl/v2")
    dsl = dimcli.Dsl()
    queryDimension= f"""search publications where doi="{doi}" return publications [title+journal+times_cited+date+altmetric+reference_ids]""" 
    dataDimension = dsl.query(queryDimension) 
    result=''
    nb=0
    text=''
    if len(dataDimension['publications']) >0 :
        text='References for <i>'
        # title
        if 'title' in dataDimension['publications'][0] :
            text=text+dataDimension['publications'][0]['title']
        else :
            text=text+doi
        text=text+'</i>'
        pub=0
        if 'date' in dataDimension['publications'][0] :
            text=text+" published on <i>"+dataDimension['publications'][0]['date']+'</i>'
            pub=1
        if 'journal' in dataDimension['publications'][0] and 'title' in dataDimension['publications'][0]['journal']:
            if pub==0:
                text=text+" published in "
            else :
                text=text+" in "
            text='<p><i>'+text+dataDimension['publications'][0]['journal']['title']+'</i></p>'
        if 'reference_ids' in dataDimension['publications'][0] and len(dataDimension['publications'][0]['reference_ids'])>0 :            
            referencesList=dataDimension['publications'][0]['reference_ids']
            referencesListString = ", ".join([f'"{ref_id}"' for ref_id in referencesList])
            queryReference= f"""search publications where id in [{referencesListString}] return publications[title+doi+journal+date] limit 1000""" 
            dataReference = dsl.query(queryReference)
            text=text+('<p>'+str(len(referencesList))+' references</p>')
            for reference in dataReference['publications']:
                if 'doi' in reference :
                    sqlExists="SELECT * FROM `retraction` WHERE `doi`='https://doi.org/%s'" % (reference['doi'])
                    cursor.execute(sqlExists)
                    dataExists = cursor.fetchall()
                    exists = len(dataExists)
                    if exists!=0:
                        sup=''
                        if 'date' in reference :
                            sup=sup+' published on '+reference['date']
                        if isinstance(dataExists[0][5], (datetime, date)) and dataExists[0][5].year!=0 :
                            sup=sup+' retracted on '+str(dataExists[0][5].year)+"-"+str(dataExists[0][5].strftime('%m'))+'-'+str(dataExists[0][5].strftime('%d'))
                        result=result+'<li><a href="https://doi.org/'+reference['doi']+'" target="_blank">'+reference['title']+'</a>'+sup+'</li>'
                        nb=nb+1
            if result=='' :
                text=text+'<p>No retracted reference found</p>'
            else :
                text=text+'<p>'+str(nb)+' retracted references found</p><ol>'+result+'</ol>'
        else :
            text=text+'<p>No retracted reference found</p>'
            
    else :
        text=text+"<p>No data found for this publication</p>"
    print('|||||'+text)
