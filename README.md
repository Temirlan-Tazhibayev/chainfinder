# chainfinder

# An application created to analyze the chains of cryptocurrency transactions, in the current version it works with the Bitcoin cryptocurrency. 
# An application developed as part of a bachelor's thesis "Asset recovery of virtual assets (national and International)" practical part by students of the specialty Cybersecurity of Astana IT University.

# The application is written on the Model-View-Controller design pattern, in the PHP language (version 8 and above).
# - The application has two functions automatically uploading to the database Bitcoin dump files from Blockchair (https://blockchair.com/dumps).
# - Displaying data on cryptocurrency transactions in graphical form for analysis.
# - All data from Bitcoin dump draw network graph using D3.JS 

# For the web application to work, you need the following:
# - Create a database named Chainfinder in postgresql (work with postgres v14 & v15)
# - Set up config.php to connect to the postgres database.
# - In php.ini, change the max_execution_time parameter to 0 so that the data from the Blockchair dump is successfully loaded into the database.  

# Functional: 
# To interact with a graph while chainalysis there you can find specific cryptocurrency wallets (sender & receiver), 
#   edit sender/receiver count to see an overall picture of virtual currency transaction, and choose time period of the transaction. 
