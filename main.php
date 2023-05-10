<?php require("config.php") ?>
<?php require("headers/headers.php") ?>

<body>
    <div class="container-fluid">
        <div class="row">
          <div class="col-sm-3">
            <div class="scrollable-container">

            </div>
          </div>
          <div class="col-sm-6">
            <div id="graph-container">

            </div>
          </div>
          <div class="col-sm-3">
            <div class="form-container">
              <h2>Bitcoin Transaction Search</h2>
              <form action="" method="post">
                <label for="input-address">Input Address:</label>
                <input type="text" name="input-address" id="input-address" placeholder="Enter input address">
                <label for="output-address">Output Address:</label>
                <input type="text" name="output-address" id="output-address" placeholder="Enter output address">
                <input type="submit" value="Search">
              </form>

              <form action="" method="post">
                <label for="transaction-count">Transaction Count:</label>
                <input type="number" name="transaction-count" id="transaction-count" placeholder="Enter transaction count">
                <input type="submit" value="Search">
              </form>
            </div>
          </div>

        </div>
    </div>


    <?php
      $inputAddress = $_POST['input-address'] ?? '';
      $outputAddress = $_POST['output-address'] ?? '';

      $transactioncount = $_POST['transaction-count'] ?? '50';


      if (!empty($inputAddress) && !empty($outputAddress)) {
        $query = "SELECT a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient as sender, b.recipient as receiver FROM inputs a
        INNER JOIN outputs b ON a.transaction_hash = b.transaction_hash WHERE a.recipient = ? AND b.recipient = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute(array($inputAddress, $outputAddress));
      } elseif (!empty($inputAddress)) {
          $query = "SELECT a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient as sender, b.recipient as receiver FROM inputs a
          INNER JOIN outputs b ON a.transaction_hash = b.transaction_hash WHERE a.recipient = ?";
          $stmt = $conn->prepare($query);
        $stmt->execute(array($inputAddress));
      }
      elseif (!empty($outputAddress))
      {
          $query = "SELECT a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient as sender, b.recipient as receiver FROM inputs a
          INNER JOIN outputs b ON a.transaction_hash = b.transaction_hash WHERE a.recipient = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute(array($outputAddress));
      }

      else
      {
          $query = "SELECT a.block_id, a.transaction_hash, a.time, a.value_usd, a.recipient as sender, b.recipient as receiver FROM inputs a
          INNER JOIN outputs b ON a.transaction_hash = b.transaction_hash LIMIT $transactioncount";
          $stmt = $conn->query($query);
      }
      // преобразование результата в массив ассоциативных массивов
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      // закрытие соединения с базой данных
      $conn = null;
      // преобразование массива в JSON-строку
      $json = json_encode($rows);
?>



<script>
// Set up the data as an array of objects
// const data = [
//     {"input": "176M1Swy2AoUVByGpYHWy4QtJtvYs5fPqf", "transaction_hash": "d5339cb3c50c04353480b5bb4e7d67d4d18d7a844460eb40da2dfd994ae60747", "output": "176M1Swy2AoUVByGpYHWy4QtJtvYs5fPqf"},
//     {"input": "1Lm2TdUTaq6n6gWyLLqm8VTB36uZyK3BHM", "transaction_hash": "92883d2353af58b4df72ba7b74f80c525d6dd612de3f8b4575bfc8879b117fd7", "output": "1DrUT9tvkarcrxdyfgGrrWjgcCHZVHDYHM"},
//     {"input": "1Lm2TdUTaq6n6gWyLLqm8VTB36uZyK3BHM", "transaction_hash": "92883d2353af58b4df72ba7b74f80c525d6dd612de3f8b4575bfc8879b117fd7", "output": "1Lm2TdUTaq6n6gWyLLqm8VTB36uZyK3BHM"},
//     ...
// ]

    var data = <?php echo $json ?>;
    const width = 1200;
    const height = 800;

    const svg = d3.select('#graph-container')
      .append('svg')
        .attr('width', width)
        .attr('height', height);

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

    // // Создаем div-элемент для отображения таблицы
    // const tableContainer = d3.select('body')
    //     .append('div')
    //     .attr('id', 'table-container')
    //     .classed('scrollable-container', true);



      function displayNodeInfo(nodeData) {
        // Удаляем предыдущие карточки, если они существуют
        const scrollableContainer = d3.select('.scrollable-container');
        scrollableContainer.selectAll('.card').remove();

        // Создаем карточки и заполняем их данными
        nodeData.forEach(data => {
          const card = scrollableContainer.append('div')
            .attr('class', 'card');
          const cardHeader = card.append('div')
            .attr('class', 'card-header')
            .append('h5')
            .text(data.transaction_hash);
          const cardBody = card.append('div')
            .attr('class', 'card-body');
          const table = cardBody.append('table')
            .attr('class', 'table');
          const tbody = table.append('tbody');

          const rowsData = [
            { label: 'Block ID', value: data.block_id },
            { label: 'Time', value: data.time },
            { label: 'Sender', value: data.sender },
            { label: 'Receiver', value: data.receiver },
            { label: 'Value USD', value: data.value_usd },
          ];

          const rows = tbody.selectAll('tr')
            .data(rowsData)
            .join('tr');

          rows.append('td')
            .html(d => `<strong>${d.label}:</strong> ${d.value}`);
        });
      }




    // update the links and nodes when data changes
    function update(data) {
      const links = data.map(d => ({ source: d.sender, target: d.receiver }));
      const nodeIDs = new Set(data.flatMap(d => [d.sender, d.receiver]));
      const nodes = Array.from(nodeIDs).map(id => ({
        id: id,
        data: data.filter(item => item.sender === id || item.receiver === id)
      }));
      // Обновление links
      const link = linkGroup
        .selectAll('line')
        .data(links, d => d.source.id + '-' + d.target.id)
        .join('line')
          .attr('stroke', '#999')
          .attr('stroke-opacity', 0.6)
          .attr('stroke-width', d => Math.sqrt(d.value));


      // Обновление nodes
      const node = nodeGroup
        .selectAll('circle')
        .data(nodes, d => d.id)
        .join('circle')
          .attr('r', 5)
          .attr('fill', '#000')
          .on('click', d => displayNodeInfo(d.target['__data__']['data']));


      const simulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).id(d => d.id))
        .force('charge', d3.forceManyBody())
        .force('center', d3.forceCenter(width / 2, height / 2));

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
  </body>
</html>
