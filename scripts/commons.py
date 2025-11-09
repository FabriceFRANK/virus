#########################################################################################################################
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#                                          Commons functions for VIRUS project                                          #
#                                                                                                                       #
#                                                                                                                       #
#                                                                                                                       #
#########################################################################################################################

# Import
import json

# JSON parser function
def parseJSON(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        for line in f:
            if line.strip():  # Ensure the line is not empty
                yield json.loads(line)

# Logging function
def logInfo(s,file) :
    print(s)
    with open(file, "a", encoding="utf-8") as f:
        f.write(s+"\n")

# Return Null for database if empty
def emptyNull(value) :
    if value=="" :
        value="Null"
    return value

# Functions for hanfling citations and references 
def updateCitationsReference(doi, dataReference, cursor, connection, conv, table) :
    for reference in dataReference['publications']:

        # doi       
        doiReference=''
        if 'doi' in reference:
            doiReference=reference['doi']
            titleReference=''
            pubDateReference="Null"

            # title
            if 'title' in reference:
                titleReference=reference['title']

            # Publication date
            if 'date' in reference:
                pubDateReference=reference['date']

            # Journal
            journalReference='Unknown'
            if 'journal' in reference:
                if 'journal' in reference and 'title' in reference['journal']:
                    journalReference=reference['journal']['title']
                    journalReferenceIdDimension=reference['journal']['id']
            sqlExists = "select * from journal where name='%s'" % (conv.escape(journalReference))
            cursor.execute(sqlExists)
            dataExists = cursor.fetchall()
            exists = len(dataExists)
            if exists==0:
                sqlInsert="INSERT INTO `journal` (`name`, `dimensionId`) VALUES ('%s','%s')" % (conv.escape(journalReference),journalReferenceIdDimension)
                cursor.execute(sqlInsert)
                connection.commit()
                journalReferenceId=cursor.lastrowid
            else :
                journalReferenceId=dataExists[0][0] 

            sqlExists="SELECT * FROM `%s` WHERE `doi`='https://doi.org/%s' AND `pubDoi`='https://doi.org/%s'" % (table, doi, doiReference)
            cursor.execute(sqlExists)
            dataExists = cursor.fetchall()
            exists = len(dataExists)
            if exists==0:
                sqlReference="INSERT INTO `%s` (`doi`,`pubDoi`,`title`,`pubDate`,`idJournal`) VALUES ('https://doi.org/%s','https://doi.org/%s','%s','%s',%s)" % (table, doiReference, doi, conv.escape(titleReference),pubDateReference,journalReferenceId)
                cursor.execute(sqlReference)
                connection.commit()