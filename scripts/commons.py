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
