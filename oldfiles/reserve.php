<html lang="en">
<head>
<head>
  <meta charset="UTF-8">
  <title>My D3.js Page</title>
  <script src="d3.v7.min.js"></script>
  <script src="d3-drag.v2.min.js"></script>

</head>
<style>
      #graph-container {
        margin: auto;
        width: 1200px;
        height: 800px;
        border: 1px solid #ddd;
      }
      
      .form-container {
        margin: auto;
        width: 400px;
        padding: 20px;
        border: 1px solid #ddd;
      }
      
      .form-container label, .form-container input {
        display: block;
        margin-bottom: 10px;
      }
      
      .form-container input[type="submit"] {
        margin-top: 10px;
        padding: 5px 10px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
      }
      
      .form-container input[type="submit"]:hover {
        background-color: #3e8e41;
      }
    </style>

</head>

<body>
    <div style="text-align:center">
        <div id="graph-container"></div>
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
    <div id="sidebar">

    </div>
    <?php
      // параметры подключения к базе данных MySQL
      $host = 'localhost';
      $dbname = 'bitcoin';
      $user = 'root';
      $password = '';

      // создаем объект соединения с базой данных
      try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      } catch(PDOException $e) {
        echo "Ошибка подключения к базе данных: " . $e->getMessage();
        exit;
      }

      $inputAddress = $_POST['input-address'] ?? '';
      $outputAddress = $_POST['output-address'] ?? '';

      $transactioncount = $_POST['transaction-count'] ?? '50';


      if (!empty($inputAddress) && !empty($outputAddress)) {
        $query = "SELECT input as input, hash as transaction_hash, output as output FROM transaction WHERE input = ? AND output = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute(array($inputAddress, $outputAddress));
      } elseif (!empty($inputAddress)) {
          $query = "SELECT input as input, hash as transaction_hash, output as output FROM transaction WHERE input = ?";
          $stmt = $conn->prepare($query);
        $stmt->execute(array($inputAddress));
      } elseif (!empty($outputAddress)) {
          $query = "SELECT input as input, hash as transaction_hash, output as output FROM transaction WHERE output = ?";

        $stmt = $conn->prepare($query);
        $stmt->execute(array($outputAddress));
      } else {
          $query = "SELECT input as input, hash as transaction_hash, output as output FROM transaction LIMIT $transactioncount";
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


function displayNodeInfo(d) {
  const sidebar = d3.select('#sidebar');
  sidebar.html(''); // Очистка предыдущей информации

  // Найти все объекты данных, связанные с выбранным узлом
  const relatedData = data.filter(item => item.input === d.id || item.output === d.id);

  if (relatedData.length > 0) {
    // Добавление информации об узле
    sidebar.append('h2').text(`Node: ${d.id}`);

    relatedData.forEach(item => {
      sidebar.append('p').text(`Input: ${item.input}`);
      sidebar.append('p').text(`Transaction Hash: ${item.transaction_hash}`);
      sidebar.append('p').text(`Output: ${item.output}`);
      sidebar.append('hr');
    });
  } else {
    sidebar.append('h2').text(`Node: ${d.id}`);
    sidebar.append('p').text('No additional information available.');
  }
}



// update the links and nodes when data changes
function update(data) {
  const links = data.map(d => ({ source: d.input, target: d.output }));
  const nodeIDs = new Set(data.flatMap(d => [d.input, d.output]));
  // const nodes = Array.from(nodeIDs).map(id => ({ id }));
  const nodes = Array.from(nodeIDs).map(id => ({ id: id }));

  // Обновление links
  const link = linkGroup
    .selectAll('line')
    .data(links, d => d.source.id + '-' + d.target.id) // добавьте ключ данных для обновления
    .join('line')
      .attr('stroke', '#999')
      .attr('stroke-opacity', 0.6)
      .attr('stroke-width', d => Math.sqrt(d.value));

  // Обновление nodes
  const node = nodeGroup
    .selectAll('circle')
    .data(nodes, d => d.id) // добавьте ключ данных для обновления
    .join('circle')
      .attr('r', 5)
      .attr('fill', '#000')
      .on('click', d => displayNodeInfo(d)); // Добавление обработчика событий click


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
