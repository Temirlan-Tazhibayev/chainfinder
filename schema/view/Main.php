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

    function createHeader(nodeId, txLength){
        const scrollableContainer = d3.select('.scrollable-container');
        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-all')
            .append('h5')
            .text("Wallet: ")
            .append('a') 
            .attr('href', `https://blockchair.com/bitcoin/address/${nodeId}`) 
            .attr('target', '_blank')
            .text(nodeId);

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('h5')
            .text(`Number of Transactions: ${txLength}`)
            
    }
    
    function getUniqueTransactions(nodeId, nodeData) {

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
            walletsArray.forEach(({wallet, value, value_usd}) => {
                const walletKey = `${wallet}-${value}-${value_usd}`;
                if (!uniqueData.has(walletKey)) {
                    uniqueData.set(walletKey, {wallet, value, value_usd});
                }
            });
            return Array.from(uniqueData.values());
        }

        function getNodeOperations(wallets){

            // Filter the wallets that match the node ID
            let nodeOperations = wallets.filter(wallet => wallet.wallet === nodeId);

            // Map over the filtered wallets and return an object of value and value_usd
            return nodeOperations.map(wallet => {
                return { 
                    value: wallet.value, 
                    value_usd: wallet.value_usd 
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
                return (Math.max(senderSum, receiverSum) / 100000000).toString();
            }else if (property == 'value_usd'){
                return Math.max(senderSum, receiverSum).toFixed(2);
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
                totalUsd = calculateTotal(senderWallets, receiverWallets, 'value_usd');

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

        
        nodeData.forEach(data => {
            blockId = data.block_id;
            time = data.time;
            if (data.transaction_hash != transactionFlag) {
                processTransaction();
                
                transactionFlag = data.transaction_hash;
                senderWallets = [{ wallet: data.sender, value: data.sent_value, value_usd: data.sent_usd}];
                receiverWallets = [{ wallet: data.receiver, value: data.received_value, value_usd: data.received_usd}];
            }else{
                senderWallets.push({ wallet: data.sender, value: data.sent_value, value_usd: data.sent_usd})
                receiverWallets.push({ wallet: data.receiver, value: data.received_value, value_usd: data.received_usd})

            }
        });

        processTransaction();
        return uniqueTransactions;
    }
    
    // Create Card with Header and Body
    function createCard(transactionHash, allData) {
        const scrollableContainer = d3.select('.scrollable-container');
        const card = scrollableContainer.append('div').attr('class', 'card');
        // put public keys (addresses) of all wallets into one array
        let wallets = allData.receiverWallets.map(obj => obj.wallet);
        wallets.push(...allData.senderWallets.map(obj => obj.wallet));

        addCardHeader(card, transactionHash, wallets);
        addCardBody(card, allData);
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
    function addCardBody(card, allData) {
        const transactionData = allData.transactionInfo;
        const table = card.append('table').attr('class', 'table table-striped');
        const tbody = table.append('tbody');
        
        const labels = [
            { label: 'Total (Bitcoin)', value:  transactionData.totalBitcoins },
            { label: 'Total (USD)', value:  transactionData.totalUsd },
            { label: 'Block ID', value: transactionData.blockId, url: `https://blockchair.com/bitcoin/block/${transactionData.blockId}` },
            { label: 'Time', value: transactionData.time }
        ];

        const sendersHeader = `Total Inputs (${transactionData.sendersNumber})`;
        const receiversHeader = `Total Outputs (${transactionData.receiversNumber})`;
    
        addRowsToTableBody(tbody, labels);

        // Add transaction inputs and outputs of the node wallet only
        if (nodeInputs.length > 0){
            addCollapsibleList(tbody, `Wallet Inputs (${nodeInputs.length})`, allData.nodeInputs);
        }
        if (nodeOutputs.length > 0){
            addCollapsibleList(tbody, `Wallet Outputs (${nodeOutputs.length})`, allData.nodeOutputs);
        }

        // Add all inputs and outputs of the transaction
        addCollapsibleList(tbody, sendersHeader, allData.senderWallets);
        addCollapsibleList(tbody, receiversHeader, allData.receiverWallets);
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
                item.append("strong").text(`Bitcoin: ${d.value/100000000}, USD: ${Number(d.value_usd).toFixed(2)}`);
                item.append("p")
                    .text("show")
                    .on("click", function() {
                        highlightNode(d.wallet, "wallet");
                    });
            } else {
                item.append("hr").attr("class", "dashed");
                item.append("strong").text(`Bitcoin: ${d.value/100000000}, USD: ${Number(d.value_usd).toFixed(2)}`);
            }
        });
        
        listHeader.on('click', function () {
            const displayStyle = listItems.style('display');
            listItems.style('display', displayStyle === 'none' ? 'block' : 'none');
        });
    }
    
    
    // Main function that displays data on the left window
    function displayNodeInfo(nodeId, nodeData) {
        removePreviousData();
        const transactionCount = (function(arr) {
          const distinctNames = new Set(arr.map(obj => obj.transaction_hash));
          return distinctNames.size;
        })(nodeData);

        createHeader(nodeId, transactionCount);
    
        const uniqueTransactions = getUniqueTransactions(nodeId, nodeData);
    
        uniqueTransactions.forEach((allData, transactionHash) => {
            createCard(transactionHash, allData);
        });
    }
/* *********************************************************************************************************************************************************************************** */


    function getRadius(d) {
      const minRadius = 5;
      const maxRadius = 12;
      const numParticipants = d.data.length;

      if (numParticipants <= 3) {
        return minRadius;
      } else {
        return Math.min(minRadius + (numParticipants - 3), maxRadius);
      }
    }

    function isLargeNode(d) {
      return d.data.length > 3;
    }

    function getTransactionsByWallet(transactions, wallet) {
        // Find all the transaction hashes where the wallet is involved
        const involvedTransactions = transactions.filter((transaction) => 
            transaction.sender === wallet || transaction.receiver === wallet
        ).map((transaction) => 
            transaction.transaction_hash
        );
        
        // Now, return all transactions which have transaction_hash present in involvedTransactionHashes
        return transactions.filter((transaction) => 
            involvedTransactions.includes(transaction.transaction_hash)
        );
    }

    function setColor(d){
        let nodeData = d.data;
        let nodeId = d.id;
        let isSender = false;
        let isReceiver = false;
        
        for (let i = 0; i < nodeData.length; i++) {
            if (nodeData[i]["sender"] === nodeId) {
                isSender = true;
            }
            if (nodeData[i]["receiver"] === nodeId) {
                isReceiver = true;
            }
            if (isReceiver && isSender){
                return '#EE82EE'; // Violet
            }
        }
    
        if (isReceiver){
            return '#5AC6F2'; // SkyBlue
        }
        else if (isSender){
            return '#32CD32'; // LimeGreen
        }
        else{
            return '#808080'; // Gray
        }
    }


    function zoomToNode(nodeToFocus){
        // Suppose `x` and `y` are the coordinates of the node to focus on
        let x = nodeToFocus.x;
        let y = nodeToFocus.y;

        // And `zoom` is the d3.zoom object associated with your SVG element
        let svg = d3.select('svg');

        svg.transition()
            .duration(1000) // transition duration of 1 second
            .call(zoom.transform, d3.zoomIdentity.translate(-x, -y));
    }

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

    // update the links and nodes when data changes
    function update(data) {
        const nodeIDs = new Set(data.flatMap(d => [d.sender, d.receiver]));
            
        const nodes = Array.from(nodeIDs).map(id => ({
            id: id,
            data: getTransactionsByWallet(data, id)
        }));
        
        console.log(nodes[0]);
        // Create links from data
        const links = [];
        for (let i = 0; i < data.length; i++) {
            const { sender, receiver } = data[i];
            links.push({ source: sender, target: receiver });
        }

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
        
            displayNodeInfo(d.id, d.data);
        });
            

            
        const simulation = d3.forceSimulation(nodes)
            .force('link', d3.forceLink(links).id(d => d.id).distance(50))
            .force('charge', d3.forceManyBody().strength(-100)) // изменено значение strength
            .force('center', d3.forceCenter(width / 2, height / 2))
            .force('collideSmall', d3.forceCollide().radius(d => isLargeNode(d) ? 0 : getRadius(d) + 2))
            .force('collideLarge', d3.forceCollide().radius(d => isLargeNode(d) ? getRadius(d) + 5 : 0));// Добавьте силу столкновения для узлов с большим количеством участников



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