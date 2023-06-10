<?php require_once WWW_PATH . '/schema/view/head.php'; 

$json = json_encode($data);


?>
<!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<style>
    hr.dashed {
	    border-top: 1px dashed #8c8b8b;
    }
</style>
</head>
<body>
  
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3">
            <h2>Transactions</h2>
            <div class="scrollable-container">
            </div>
        </div>
        <div class="col-sm-6">
            <h2>Graph</h2>
            <div id="graph-container"></div>
        </div>
        <div class="col-sm-3">
            <div class="form-container">
                <h3>Bitcoin Transaction Search</h3>
                <form action="" method="post">
                    <br>
                    <label for="transaction-hash">Transaction hash:</label>
                    <div class="input-group">
                        <input class="form-control" type="text" value="<?php echo isset($_SESSION['transactionHash']) ? htmlspecialchars($_SESSION['transactionHash']) : ''; ?>" name="transaction-hash" id="transaction-hash" placeholder="Enter transaction hash">
                    </div>
        
                    <hr>              
            
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="input-address">Sender Address:</label>
                            <div class="input-group">
                                <input class="form-control" type="text" value="<?php echo isset($_SESSION['inputAddress']) ? htmlspecialchars($_SESSION['inputAddress']) : ''; ?>" name="input-address" id="input-address" placeholder="Enter input address">
                            </div>
                        </div>
        
                        <div class="col-sm-6">
                            <label for="output-address">Receiver Address:</label>
                            <div class="input-group">
                                <input class="form-control" type="text" value="<?php echo isset($_SESSION['outputAddress']) ? htmlspecialchars($_SESSION['outputAddress']) : ''; ?>" name="output-address" id="output-address" placeholder="Enter output address">
                            </div>
                        </div>
                    </div>
        
                    <hr>              
            
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="block-id">Block ID:</label>
                            <div class="input-group">
                                <input class="form-control" type="number" value="<?php echo isset($_SESSION['blockId']) ? htmlspecialchars($_SESSION['blockId']) : ''; ?>" name="block-id" id="block-id" placeholder="Enter block ID">
                            </div>
                        </div>
        
                        <div class="col-sm-6">
                            <label for="transaction-count">Connections number:</label>
                            <div class="input-group">
                                <input class="form-control" type="number" value="<?php echo isset($_SESSION['transactionCount']) ? htmlspecialchars($_SESSION['transactionCount']) : ''; ?>" name="transaction-count" id="transaction-count" placeholder="Enter transaction count">
                            </div>
                        </div>
                    </div>
        
                    <hr>
        
                    <div>            
                        <h5>Transaction period:</h5>
                        <div class="row">
                            <div class="col-sm-6">
                                <label for="start-timestamp">From:</label>
                                <div class="input-group">
                                    <input class="form-control" type="datetime-local" id="start-timestamp" name="start-timestamp" value="<?php echo isset($_SESSION['startTimestamp']) ? htmlspecialchars($_SESSION['startTimestamp']) : ''; ?>">
                                </div>
                            </div>
        
                            <div class="col-sm-6">
                                <label for="end-timestamp">Until:</label>
                                <div class="input-group">
                                    <input class="form-control" type="datetime-local" id="end-timestamp" name="end-timestamp" value="<?php echo isset($_SESSION['endTimestamp']) ? htmlspecialchars($_SESSION['endTimestamp']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mt-2">
                        <input class="form-control btn btn-primary" type="submit" value="Search">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script>
    var data = <?php echo $json ?>;
    data = Array.from(new Set(data.map(JSON.stringify))).map(JSON.parse);
    const width = 100;
    const height = 100;

    const svg = d3.select('#graph-container')
      .append('svg')
        .attr('viewBox', `0 0 ${width} ${height}`)
        .attr('preserveAspectRatio', 'xMinYMin meet')
        .style('width', '100%')
        .style('height', '100%');


    const g = svg.append('g');

    // Создайте группы для nodes и links перед функцией update
    const linkGroup = g.append('g');
    const nodeGroup = g.append('g');

    // Добавление возможности масштабирования к SVG
    const zoom = d3.zoom().on('zoom', zoomed);
    svg.call(zoom);

    // Функция для обработки событий масштабирования
    function zoomed(event) {
      g.attr('transform', event.transform);
    }


/* *********************************************************************************************************************************************************************************** */

    function removePreviousData() {
        const scrollableContainer = d3.select('.scrollable-container');
        scrollableContainer.selectAll('.header').remove();
        scrollableContainer.selectAll('.card').remove();
    }

    function createHeader(nodeData){
        console.log(nodeData);
        const transactionCount = nodeData.transactions.size;
        
        const scrollableContainer = d3.select('.scrollable-container');
        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-all')
            .append('h5')
            .text("Wallet: ")
            .append('a') 
            .attr('href', `https://blockchair.com/bitcoin/address/${nodeData.id}`) 
            .attr('target', '_blank')
            .text(nodeData.id);

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('h5')
            .text(`Number of Transactions: ${transactionCount}`)

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('h5')
            .text(`Sent: ${nodeData.totalSent.value/100000000} BTC | ${nodeData.totalSent.valueUsd.toFixed(2)} USD`)

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('h5')
            .text(`Received: ${nodeData.totalReceived.value/100000000} BTC | ${nodeData.totalReceived.valueUsd.toFixed(2)} USD`)
            
    }
    
    // Create Card with Header and Body
    function createCard(transactionHash, transactionData) {
        const scrollableContainer = d3.select('.scrollable-container');
        const card = scrollableContainer.append('div').attr('class', 'card');
        // put public keys (addresses) of all wallets into one array
        let wallets = transactionData.receiverWallets.map(obj => obj.wallet);
        wallets.push(...transactionData.senderWallets.map(obj => obj.wallet));

        addCardHeader(card, transactionHash, wallets);
        addCardBody(card, transactionData);
    }

    // Add Card Header
    function addCardHeader(card, transactionHash, wallets) {
        let cardHeader = card.append('div')
                    .attr('class', 'card-header');

        cardHeader.append('h5')
                  .text("Transaction hash: ")
                  .append('a') 
                  .attr('href', `https://blockchair.com/bitcoin/transaction/${transactionHash}`) 
                  .attr('target', '_blank')
                  .text(transactionHash);

        cardHeader.append("p")
                  .text("show")
                  .on("click", function() {
                      highlightNode(wallets, "transaction");
                  });
    }

    // Add Card Body
    function addCardBody(card, transactionData) {
        const transactionInfo = transactionData.transactionInfo;
        const table = card.append('table').attr('class', 'table table-striped');
        const tbody = table.append('tbody');
        
        const labels = [
            { label: 'Total (Bitcoin)', value:  transactionInfo.totalBitcoins },
            { label: 'Total (USD)', value:  transactionInfo.totalUsd },
            { label: 'Block ID', value: transactionInfo.blockId, url: `https://blockchair.com/bitcoin/block/${transactionInfo.blockId}` },
            { label: 'Time', value: transactionInfo.time }
        ];

        const sendersHeader = `Total Inputs (${transactionInfo.sendersNumber})`;
        const receiversHeader = `Total Outputs (${transactionInfo.receiversNumber})`;
    
        addRowsToTableBody(tbody, labels);

        // Add transaction inputs and outputs of the node wallet only
        if (nodeInputs.length > 0){
            addCollapsibleList(tbody, `Wallet Inputs (${nodeInputs.length})`, transactionData.nodeInputs);
        }
        if (nodeOutputs.length > 0){
            addCollapsibleList(tbody, `Wallet Outputs (${nodeOutputs.length})`, transactionData.nodeOutputs);
        }

        // Add all inputs and outputs of the transaction
        addCollapsibleList(tbody, sendersHeader, transactionData.senderWallets);
        addCollapsibleList(tbody, receiversHeader, transactionData.receiverWallets);
    }
    
    function addRowsToTableBody(tbody, labels) {
        const rows = tbody.selectAll('tr').data(labels).join('tr');
    
        rows.append('td')
            .html(d => d.url ? `<strong>${d.label}:</strong> <a href="${d.url}" target="_blank">${d.value}</a>` 
                             : `<strong>${d.label}:</strong> ${d.value}`);
    }
    

    function addCollapsibleList(parentElement, headerData, listData) {
        const listGroup = parentElement
            .append('tr')
            .append('td')
            .attr('colspan', '2')
            .style('word-break', 'break-all');
    
        const listHeader = listGroup.append('div')
            .text(headerData)
            .style('cursor', 'pointer')
            .style('word-break', 'break-word')
            .attr('class', 'text-info');
    
        const listItems = listGroup.selectAll('div.list-item')
            .data(listData)
            .join('div')
            .attr('class', 'list-item')
            .style('display', 'none')

        listItems.each(function (d) {
            const item = d3.select(this);
            if (d.wallet) {
                item.append("hr").attr("class", "dashed");
                item.append("a")
                    .attr("href", "https://blockchair.com/bitcoin/address/" + d.wallet)
                    .attr("target", "_blank")
                    .attr("class", "text-success")
                    .text(d.wallet);
                item.append("br");
                item.append("strong").text(`Bitcoin: ${d.value/100000000}, USD: ${Number(d.valueUsd).toFixed(2)}`);
                item.append("p")
                    .text("show")
                    .on("click", function() {
                        highlightNode(d.wallet, "wallet");
                    });
            } else {
                item.append("hr").attr("class", "dashed");
                item.append("strong").text(`Bitcoin: ${d.value/100000000}, USD: ${Number(d.valueUsd).toFixed(2)}`);
            }
        });
        
        listHeader.on('click', function () {
            const displayStyle = listItems.style('display');
            listItems.style('display', displayStyle === 'none' ? 'block' : 'none');
        });
    }
    
    
    // Main function that displays data on the left window
    function displayNodeInfo(nodeData) {
        removePreviousData();
    
        createHeader(nodeData);
    
        nodeData.transactions.forEach((transactionData, transactionHash) => {
            createCard(transactionHash, transactionData);
        });
    }
/* *********************************************************************************************************************************************************************************** */

    function getUniqueTransactions(nodeId, data) {
        
        const involvedTransactions = data.filter((transaction) => 
            transaction.sender === nodeId || transaction.receiver === nodeId
        ).map((transaction) => 
            transaction.transaction_hash
        );
        const uniqueTransactions = new Map();

        let blockId = null;
        let time = null;
        let transactionFlag = null;
        let senderWallets = [];
        let receiverWallets = [];
        let totalBitcoins = 0;
        let totalUsd = 0;

        function getUniqueWallets(walletsArray) {
            const uniqueData = new Map();
            walletsArray.forEach(({wallet, value, valueUsd}) => {
                const walletKey = `${wallet}-${value}-${valueUsd}`;
                if (!uniqueData.has(walletKey)) {
                    uniqueData.set(walletKey, {wallet, value, valueUsd});
                }
            });
            return Array.from(uniqueData.values());
        }

        function getNodeOperations(wallets){
        
            // Filter the wallets that match the node ID
            let nodeOperations = wallets.filter(wallet => wallet.wallet === nodeId);
        
            // Map over the filtered wallets and return an object of value and valueUsd
            return nodeOperations.map(wallet => {
                return { 
                    value: wallet.value, 
                    valueUsd: parseFloat(wallet.valueUsd)
                };
            });
        }

        // Helper function for calculateTotal()  to sum up all values of each wallet
        function calculateSum(wallets, property) {
            return wallets.reduce((sum, wallet) => sum + Number(wallet[property]), 0);
        }

        // We compare totals of inputs and outputs in case one of them is incomplete
        function calculateTotal(senderWallets, receiverWallets, property) {
            const senderSum = calculateSum(senderWallets, property);
            const receiverSum = calculateSum(receiverWallets, property);
            if (property == 'value'){
                return (Math.max(senderSum, receiverSum) / 100000000);
            }else if (property == 'valueUsd'){
                return parseFloat(Math.max(senderSum, receiverSum).toFixed(2));
            }
        }

        // As internal function, it has access to variables of outer function
        function processTransaction() {
            if (senderWallets.length > 0 && receiverWallets.length > 0) {
                senderWallets = getUniqueWallets(senderWallets);
                receiverWallets = getUniqueWallets(receiverWallets);
                nodeInputs = getNodeOperations(senderWallets);
                nodeOutputs = getNodeOperations(receiverWallets);
                totalBitcoins = calculateTotal(senderWallets, receiverWallets, 'value');
                totalUsd = calculateTotal(senderWallets, receiverWallets, 'valueUsd');
            
                const transactionInfo = {
                    transactionHash: transactionFlag,
                    totalBitcoins: totalBitcoins,
                    totalUsd: totalUsd,
                    sendersNumber: senderWallets.length,
                    receiversNumber: receiverWallets.length,
                    blockId: blockId,
                    time: time,
                };
                uniqueTransactions.set(transactionFlag, {senderWallets, receiverWallets, nodeInputs, nodeOutputs, transactionInfo});
            }
        }


        data.forEach(transaction => {
            if (involvedTransactions.includes(transaction.transaction_hash)){
                blockId = transaction.block_id;
                time = transaction.time;
                if (transaction.transaction_hash != transactionFlag) {
                    processTransaction();

                    transactionFlag = transaction.transaction_hash;
                    senderWallets = [{ wallet: transaction.sender, value: transaction.sent_value, valueUsd: transaction.sent_usd}];
                    receiverWallets = [{ wallet: transaction.receiver, value: transaction.received_value, valueUsd: transaction.received_usd}];
                }else{
                    senderWallets.push({ wallet: transaction.sender, value: transaction.sent_value, valueUsd: transaction.sent_usd})
                    receiverWallets.push({ wallet: transaction.receiver, value: transaction.received_value, valueUsd: transaction.received_usd})
                
                }
            }
        });

        processTransaction();
        return uniqueTransactions;
    }


    function getRadius(nodeData) {
      totalSentUsd = nodeData.totalSent.valueUsd;
      totalReceivedUsd = nodeData.totalReceived.valueUsd;
      const difference = Math.abs(totalSentUsd - totalReceivedUsd);

      if (difference <= 100) {
        return 4;
      } else if (difference <= 1000) {
        return 7;
      }else if (difference <= 10000) {
        return 9;
      }else if (difference <= 100000) {
        return 11;
      }else if (difference <= 500000) {
        return 13;
      }else{
        return 15;
      }
    }

    // function getTransactionsByWallet(transactions, wallet) {
    //     // Find all the transaction hashes where the wallet is involved
    //     const involvedTransactions = transactions.filter((transaction) => 
    //         transaction.sender === wallet || transaction.receiver === wallet
    //     ).map((transaction) => 
    //         transaction.transaction_hash
    //     );
        
    //     // Now, return all transactions which have transaction_hash present in involvedTransactionHashes
    //     return transactions.filter((transaction) => 
    //         involvedTransactions.includes(transaction.transaction_hash)
    //     );
    // }

    function setColor(nodeData){
        let totalSent = nodeData.totalSent.value;
        let totalReceived = nodeData.totalReceived.value;

        if (totalSent > 0 && totalReceived > 0){
                return '#EE82EE'; // Violet
        }
        else if (totalReceived > 0){
            return '#5AC6F2'; // SkyBlue
        }
        else if (totalSent > 0 ){
            return '#32CD32'; // LimeGreen
        }
        else{
            return '#808080'; // Gray
        }
    }


    // function zoomToNode(nodeToFocus){
    //     // Suppose `x` and `y` are the coordinates of the node to focus on
    //     let x = nodeToFocus.x;
    //     let y = nodeToFocus.y;

    //     // And `zoom` is the d3.zoom object associated with your SVG element
    //     let svg = d3.select('svg');

    //     svg.transition()
    //         .duration(1000) // transition duration of 1 second
    //         .call(zoom.transform, d3.zoomIdentity.translate(-x, -y));
    // }

    function highlightNode(selectedNode, type) {
        node = d3.selectAll('circle');
        node.attr('stroke', "none");

        let nodeObjects = null;
        if (type === 'wallet'){
            nodeObjects = node.filter((d) => d.id === selectedNode);
        }else if (type === 'transaction'){
            nodeObjects = node.filter((d) => selectedNode.includes(d.id));
        }

        if (nodeObjects != null){
            nodeObjects.attr('stroke', 'orange').attr('stroke-width', 3).attr('stroke-opacity', 1);
        }else{
            alert("Some internal error occured! The wallet you have chosen doesn't exist in the graph!");
        }

        //zoomToNode(nodeObjects);
        //d3.select(selectedNode).attr('stroke', 'orange');
        // If there was a previously selected node, reset its color
        // if (selectedNode) {
        //     d3.select(selectedNode).attr('fill', setColor);
        // }
        // d3.select(clickedNode).attr('fill', 'red');
        // return clickedNode;
    }

    function removeDuplicates(array) {
      return array.filter((item, index) => {
        return array.indexOf(item) === index;
      });
    }

    // update the links and nodes when data changes
    function update(data) {
        const nodeIDs = new Set(data.flatMap(d => [d.sender, d.receiver]));

        // helper function
        function getTotalSum(totalSum, operations){
            for (i = 0; i < operations.length; i++){
                totalSum.value = operations[i].value;
                totalSum.valueUsd = operations[i].valueUsd;
            }
            return totalSum;
        }
        const nodes = Array.from(nodeIDs).map(id => {
            let transactions = getUniqueTransactions(id, data);
            let totalSent = {value: 0, valueUsd: 0};
            let totalReceived = {value: 0, valueUsd: 0};
            transactions.forEach((txData) => {
                totalSent = getTotalSum(totalSent, txData.nodeInputs);
                totalReceived = getTotalSum(totalReceived, txData.nodeOutputs);
            });
            return {
                id: id,
                totalSent: totalSent,
                totalReceived: totalReceived,
                transactions: transactions
            };
        });
        console.log(nodes);
        
        // Create links from data
        let links = [];
        for (let i = 0; i < data.length; i++) {
            const { sender, receiver } = data[i];
            links.push({ source: sender, target: receiver });
        }
        links = removeDuplicates(links);

        // For some reason, this method copies a bunch of other unnecessary data to a links list
        //const links = data.map(d => ({ source: d.sender.id, target: d.receiver.id }));


        // Обновление links
        var link = linkGroup
            .selectAll('line')
            .data(links, d => d.source + '-' + d.target)
            .join('line')
            .attr('stroke', '#999')
            .attr('opacity', 0.45)
            .attr('stroke-width', 0.5);

        svg.on("click", function() {
            // Reset all nodes and links to full opacity
            node.attr('fill-opacity', 1).attr('stroke', "none");
            link.attr('opacity', 0.45).attr('stroke', '#999').attr('stroke-width', 0.5);
        });
    
        // Create a map of neighboring nodes
        const neighboringNodes = links.reduce((acc, link) => {
            acc[link.source] = acc[link.source] || [];
            acc[link.target] = acc[link.target] || [];
        
            acc[link.source].push(link.target);
            acc[link.target].push(link.source);
        
            return acc;
        }, {});
    
        // variable that contain data of chosen node
        let selectedNode = null;

        // Update nodes
        var node = nodeGroup
            .selectAll('circle')
            .data(nodes, d => d.id)
            .join('circle')
                .attr('r', getRadius)
                .attr('fill', setColor)

        node.on('click', function(event, d) { 
            event.stopPropagation();
            
            // if we choose the same node again, return attributes back to initial state
            if (d === selectedNode) {
                selectedNode = null;
                node.attr('fill-opacity', 1);
                link.attr('opacity', 0.45).attr('stroke', '#999').attr('stroke-width', 0.5);
            } else {
                selectedNode = d;
            
                // Set all nodes and links to low opacity
                node.attr('fill-opacity', 0.15).attr('stroke', "none");
                link.attr('opacity', 0.1).attr('stroke', '#999').attr('stroke-width', 0.5);
            
                // Set the clicked node, neighboring nodes, and the links between them to high opacity
                neighboringNodes[d.id].forEach(neighborId => {
                    node.filter(n => n.id === neighborId).attr('fill-opacity', 0.7);
                });
                // highlight the chosen node
                d3.select(this).attr('fill-opacity', 1);

                // get links connected to the chosen node
                nodeLinks = link.filter(l => l.source.id === d.id || l.target.id === d.id);

                // link appearance changes depending on the number of neighbouring nodes
                if (nodeLinks.size() < 10){
                    nodeLinks.attr('stroke-width', 1.5).attr('opacity', 1);
                }else if (nodeLinks.size() > 80){
                    link.attr('stroke-width', 0.2);
                    nodeLinks.attr('opacity', 0.5);
                }else{
                    nodeLinks.attr('opacity', 1);
                }
                nodeLinks.attr('stroke', 'orange');
            }
        
            displayNodeInfo(d);
        });
            

            
        const simulation = d3.forceSimulation(nodes)
            .force('link', d3.forceLink(links).id(d => d.id).distance(50))
            .force('charge', d3.forceManyBody().strength(-50)) // изменено значение strength
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collide', d3.forceCollide().radius(d => getRadius(d))); // Use the node's radius for collision detection



        simulation.on('tick', () => {
          link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);

          node
            .attr('cx', d => d.x)
            .attr('cy', d => d.y);
        });

        node.append('title')
          .text(d => d.id);
    }

    update(data);

    </script>