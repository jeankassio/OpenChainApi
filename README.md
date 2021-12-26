# OpenChain
OpenChain, a PHP Blockchain API


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
	rpcpassword=someusername
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


If using an external disk for the blockchain, use the code "datadir=/youdirectory/bitcoin" above



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
	

In addition to having a webhook for withdrawals and deposits.




