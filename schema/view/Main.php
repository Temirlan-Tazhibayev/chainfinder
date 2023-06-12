<?php require_once WWW_PATH . '/schema/view/head.php';

$json = json_encode($data);

?>

<html>
<head>
<title>Page Title</title>
<style>
    hr.dashed {
	    border-top: 1px dashed white;
    }
</style>
</head>
<body class="bg-white">

<div class="container-fluid">
    <div class="row no-gutters"> <!-- no gutters = no gaps between columns -->
        <div class="col-sm-3">
            <h2 class="text-center">Transactions</h2>
            <div class="scrollable-container bg-light">
            </div>
        </div>
        <div class="col-sm-6">
            <h2 class="text-center">Graph-Map</h2>
            <div id="graph-container"></div>
        </div>
        <div class="col-sm-3">
            <h2 class="text-center">Transaction Search</h2>
            <div class="form-container">
                <form action="" method="post" class="text-dark">
                    <label for="transaction-hash">Transaction hash:</label>
                    <div class="input-group">
                        <input class="form-control" type="text" value="<?php echo isset($_SESSION['transactionHash']) ? htmlspecialchars($_SESSION['transactionHash']) : ''; ?>" name="transaction-hash" id="transaction-hash" placeholder="">
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-6">
                            <label for="input-address">Sender Address:</label>
                            <div class="input-group">
                                <input class="form-control" type="text" value="<?php echo isset($_SESSION['inputAddress']) ? htmlspecialchars($_SESSION['inputAddress']) : ''; ?>" name="input-address" id="input-address" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="output-address">Receiver Address:</label>
                            <div class="input-group">
                                <input class="form-control" type="text" value="<?php echo isset($_SESSION['outputAddress']) ? htmlspecialchars($_SESSION['outputAddress']) : ''; ?>" name="output-address" id="output-address" placeholder="">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-6">
                            <label for="block-id">Block ID:</label>
                            <div class="input-group">
                                <input class="form-control" type="number" value="<?php echo isset($_SESSION['blockId']) ? htmlspecialchars($_SESSION['blockId']) : ''; ?>" name="block-id" id="block-id" placeholder="">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="connections-count">Connections number:</label>
                            <div class="input-group">
                                <input class="form-control" type="number" value="<?php echo isset($_SESSION['connectionsCount']) ? htmlspecialchars($_SESSION['connectionsCount']) : ''; ?>" name="connections-count" id="connections-count" placeholder="">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div>
                        <h5 style="text-align: center">Transaction period</h5>
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
                    <br>
                    <div class="input-group mt-2">
                        <button class="btn btn-block bg-dark text-white" type="submit" value="Search">Search</button>
                        <!-- <div class="col-sm-6">
                            Если будет какая-та проблема с нажатием поиска нужно button > input
                        </div> -->
                    </div>
                    <br>
                    <div class="row">
                    <div class="col-12">
                        <button class="btn btn-block bg-dark text-white" type="button" onclick="highlightSearch()">Highlight on Graph</button>
                    </div>
                    <!-- <div class="col-sm-6">
                        <button class="btn btn-primary" type="button" onclick="">Highlight on Graph</button>
                    </div> -->
                </div>
                </form>
                <!-- <div class="row">
                    <div class="col-sm-6">
                        <button class="btn bg-dark text-white" type="button" onclick="highlightSearch()">Highlight on Graph</button>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
</body>
</html>

<!-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->

<script>
    var nodes;
    var links;
    var nodeIDs = [];
    // variable that contain data of chosen node
    var selectedNode = null;

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
        const transactionCount = nodeData.transactions.size;

        const scrollableContainer = d3.select('.scrollable-container');
        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-all')
          .append('span')
          .attr('style', 'font-weight: normal;')
          .text("Wallet: ")
          .append('i')
          .attr('class', 'fa fa-external-link-alt')
          .append('a')
          .attr('style', 'font-weight: 300;')
          .attr('href', `https://blockchair.com/bitcoin/address/${nodeData.id}`)
          .attr('target', '_blank')
          .text(nodeData.id);

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .attr('style', 'padding-top:15px;')
            .append('span')
            .attr('style','font-weight: normal')
            .text(`Number of Transactions: `)
            .append('span')
            .attr('style','font-weight: 300')
            .text(transactionCount)

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('span')
            .attr('style','font-weight: normal')
            .text(`Sent: `)
            .append('span')
            .attr('style','font-weight: 300')
            .text (`${nodeData.totalSent.value/100000000} BTC | ${nodeData.totalSent.valueUsd.toFixed(2)} USD`)

        scrollableContainer.append('div').attr('class', 'header').style('word-break', 'break-word')
            .append('span')
            .attr('style','font-weight: normal')
            .text(`Received: `)
            .append('span')
            .attr('style','font-weight: 300')
            .text(`${nodeData.totalReceived.value/100000000} BTC | ${nodeData.totalReceived.valueUsd.toFixed(2)} USD`)

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
                    .attr('class', 'card-header text-center');

        cardHeader.append('span')
                  .attr('style','font-weight: normal; font-size: 16px;')
                  .text("Transaction hash: ")
                  .append('a')
                  .attr('href', `https://blockchair.com/bitcoin/transaction/${transactionHash}`)
                  .attr('target', '_blank')
                  .text(transactionHash);

        cardHeader.append("button")
                  .attr('class','btn btn-sm btn-outline-dark text-center')
                  .on("click", function() {
                      highlightNode(wallets);
                  })
                  .html('<span class="font-weight-italic" style="margin-right: 5px; text-align:center;">Show/Highlight</span><i class="fa fa-eye"></i>');
    }

    // Add Card Body
    function addCardBody(card, transactionData) {
        const transactionInfo = transactionData.transactionInfo;
        const nodeInputs = transactionData.nodeInputs;
        const nodeOutputs = transactionData.nodeOutputs;

        console.log(transactionData);
        const table = card.append('table').attr('class', 'table table-striped scrolling-table');
        const tbody = table.append('tbody');

        const labels = [
            { label: 'Total (Bitcoin)', value:  (transactionInfo.totalValue/100000000) },
            { label: 'Total (USD)', value:  transactionInfo.totalValueUsd.toFixed(2) },
            { label: 'Block ID', value: transactionInfo.blockId, url: `https://blockchair.com/bitcoin/block/${transactionInfo.blockId}` },
            { label: 'Time', value: transactionInfo.time }
        ];

        const sendersHeader = `Total Inputs (${transactionInfo.sendersNumber})`;
        const receiversHeader = `Total Outputs (${transactionInfo.receiversNumber})`;

        addRowsToTableBody(tbody, labels);
        // Add transaction inputs and outputs of the node wallet only
        if (nodeInputs.length > 0){
            addCollapsibleList(tbody, `Wallet Inputs (${nodeInputs.length})`, nodeInputs);
        }
        if (nodeOutputs.length > 0){
            addCollapsibleList(tbody, `Wallet Outputs (${nodeOutputs.length})`, nodeOutputs);
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
            .attr('class','bg-dark text-white')
            .append('td')
            .attr('colspan', '2')
            .style('word-break', 'break-all');

        const listHeader = listGroup.append('div')
          .attr('class', 'rounded');

        listHeader.append('i')
          .attr('class', 'fa fa-caret-down') // Replace 'fa-icon-class' with the appropriate Font Awesome icon class
          .style('margin-right', '5px'); // Adjust the margin as needed

        listHeader.append('span')
          .text(headerData)
          .style('cursor', 'pointer')
          .style('word-break', 'break-word');


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
          .attr('style','font-weight: 300; font-size: 12px')
          .html('Show <i class="fa fa-eye fa-xs"></i>')
          .on("click", function() {
            highlightNode(d.wallet);
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
        let totalValue = 0;
        let totalValueUsd = 0;

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
        function calculateSum(wallets, type) {
            return wallets.reduce((sum, wallet) => sum + Number(wallet[type]), 0);
        }

        // We compare totals of inputs and outputs in case one of them is incomplete
        function calculateTotal(senderWallets, receiverWallets, type) {
            const senderSum = calculateSum(senderWallets, type);
            const receiverSum = calculateSum(receiverWallets, type);
            if (type == 'value'){
                return (Math.max(senderSum, receiverSum));
            }else if (type == 'valueUsd'){
                return parseFloat(Math.max(senderSum, receiverSum));
            }
        }

        // As internal function, it has access to variables of outer function
        function processTransaction() {
            if (senderWallets.length > 0 && receiverWallets.length > 0) {
                senderWallets = getUniqueWallets(senderWallets);
                receiverWallets = getUniqueWallets(receiverWallets);
                nodeInputs = getNodeOperations(senderWallets);
                nodeOutputs = getNodeOperations(receiverWallets);
                totalValue = calculateTotal(senderWallets, receiverWallets, 'value');
                totalValueUsd = calculateTotal(senderWallets, receiverWallets, 'valueUsd');

                const transactionInfo = {
                    transactionHash: transactionFlag,
                    totalValue: totalValue,
                    totalValueUsd: totalValueUsd,
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

    function highlightNode(selectedNode) {
        node = d3.selectAll('circle');
        node.attr('stroke', "none");

        //SelectedNode may be as one wallet address, as an array of wallet addresses
        let nodeObjects = null;
        nodeObjects = node.filter((d) => selectedNode.includes(d.id));

        if (nodeObjects != null){
            nodeObjects.attr('stroke', 'orange').attr('stroke-width', 3).attr('stroke-opacity', 1);
        }else{
            alert("Some internal error occured! The wallet you have chosen doesn't exist in the graph!");
        }
    }

    function highlightSearch() {
        //const nodesHighlighted = new Set();
        // let highlightNode = false;
        // console.log(nodes.map(nodeData => nodeData.id));
        // const searchTransactionHash = document.getElementById('transaction-hash').value;
        const inputAddress = document.getElementById('input-address').value;
        const outputAddress = document.getElementById('output-address').value;
        // const searchBlockId = document.getElementById('block-id').value;
        // const searchStartTimestamp = document.getElementById('start-timestamp').value;
        // const searchEndTimestamp = document.getElementById('end-timestamp').value;
        if (inputAddress != '' && outputAddress != ''){
            highlightNode([inputAddress, outputAddress]);
        }
        else if (inputAddress != ''){
            highlightNode(inputAddress);
        }else if (outputAddress != ''){
            highlightNode(outputAddress);
        }if (inputAddress != '' || outputAddress != ''){
            return;
        }else{
            $.ajax({
              type: "POST",
              url: "/GitHub/chainfinder/schema/controller/AjaxController.php", 
              dataType: 'json',
              cache: false,
              data: {
                'requestType': "highlight",
                //'displayedNodes': nodes.map(nodeData => nodeData.id),
                "transactionHash": document.getElementById('transaction-hash').value,
                // "blockId": document.getElementById('block-id').value,
                "startTimestamp": document.getElementById('start-timestamp').value,
                "endTimestamp": document.getElementById('end-timestamp').value
              },
              success: function(response) {
                console.log("JS NOW");
                if (response != "highlightAllNodes"){
                    let nodesHighlighted = new Set(response.flatMap(object => [object.sender, object.receiver])); // only keep a value if it's the first occurrence   
                    nodesHighlighted = Array.from(nodesHighlighted);
                    highlightNode(nodesHighlighted)
                    console.log(nodesHighlighted);
                }
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  console.log(jqXHR, textStatus, errorThrown);
              }
            });
        }
    }

    // update the links and nodes when data changes
    function update(data){
        nodeIDs = new Set([...nodeIDs, ...(data.flatMap(d => [d.sender, d.receiver]))]);
        console.log(nodeIDs);

        // helper function
        function getTotalSum(totalSum, operations){
            for (i = 0; i < operations.length; i++){
                totalSum.value = operations[i].value;
                totalSum.valueUsd = operations[i].valueUsd;
            }
            return totalSum;
        }
        nodes = Array.from(nodeIDs).map(id => {
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
        

        // Add unique links from data
        links = new Map();
        for (let i = 0; i < data.length; i++) {
            let obj = { source: data[i].sender, target: data[i].receiver };
            let key = data[i].sender + '-' + data[i].receiver;
            links.set(key, obj);
        }

        // Turn unique links (node pairs) to array
        links = [...links.values()];
    }
    update(data);

        // Обновление links
        var link = linkGroup
            .selectAll('line')
            .data(links)
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

    </script>
