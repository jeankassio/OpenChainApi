# OpenChain Api
OpenChain, a PHP Blockchain Bitcoin Wallet API

Connection work with EasyBitcoin.php
https://github.com/aceat64/EasyBitcoin-PHP

Donation Bitcoin: bc1qeu8eledtdn8s7et3duvp8s5tzk6ch3lkq48a4n

Tutorial Ubuntu:

** Add repository and install bitcoind ** 

	sudo apt-get install build-essential
	sudo apt-get install libtool autotools-dev autoconf
	sudo apt-get install libssl-dev
	sudo apt-get install libboost-all-dev
	sudo add-apt-repository ppa:luke-jr/bitcoincore
	sudo apt-get update
	sudo apt-get install bitcoind
	mkdir ~/.bitcoin/ && cd ~/.bitcoin/
	nano bitcoin.conf


** Add config to bitcoin.conf file ** 


	rpcuser=someusername
	rpcpassword=somepassword
	testnet=0
	rpcport=8332
	rpcallowip=127.0.0.1
	server=1
	listen=1
	daemon=1
	txindex=1
	prune=0
	addresstype=bech32
	addnode=127.0.0.1
	dbcache=2000
	walletnotify=curl "https://your_url/hook/walletnotify.php?tx=%s"
	blocknotify=curl "https://your_url/hook/blocknotify.php?tx=%s"


If using an external volume for the blockchain, use the code "datadir=/youdirectory/bitcoin" above


And activate the Load on Startup

if ubuntu version >= 18.04



	touch /etc/rc.local
 	chmod +x /etc/rc.local
	sudo systemctl enable rc-local.service
	
now, open file to edit
	
	nano /etc/rc.local

and write this following code:
	
	#!/bin/sh -e
	#
	
	sudo bitcoind
	exit 0


The api has the endpoints:

-Calculation of fee;

	https://your_url/call/calculatefee
	
-Create Wallet;

	https://your_url/call/createwallet
	
-Get balance;

	https://your_url/call/getbalance
	
-Generate new address;

	https://your_url/call/getnewaddress
	
-Get transaction info;

	https://your_url/call/gettxidinfo
	
-Withdraw;

	https://your_url/call/setnewtransaction

-Watch Address;
	
	https://your_url/call/watchaddress
	

In addition to having a webhook for withdrawals and deposits.


Inside all call files has tha explanation how to receive calls.


