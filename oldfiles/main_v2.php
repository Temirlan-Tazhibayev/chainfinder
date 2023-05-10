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

</head><body>
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

$transactioncount = $_POST['transaction-count'] ?? 50;

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
  $query = "SELECT input as input, hash as transaction_hash, output as output FROM transaction LIMIT $limit";
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

var data = <?php echo $json ?>;
const width = 1200;
const height = 800;

const svg = d3.select('#graph-container')
  .append('svg')
    .attr('width', width)
    .attr('height', height);

const gZoom = svg.append('g'); // создаем группу для зума
const gGraph = svg.append('g'); // создаем группу для графа

const links = data.map(d => ({ source: d.input, target: d.output }));

const nodeIDs = new Set(data.flatMap(d => [d.input, d.output]));

const nodes = Array.from(nodeIDs).map(id => ({ id }));

const simulation = d3.forceSimulation(nodes)
    .force('link', d3.forceLink(links).id(d => d.id))
    .force('charge', d3.forceManyBody())
    .force('center', d3.forceCenter(width / 2, height / 2));

const link = gGraph.append('g')
    .selectAll('line')
    .data(links)
    .join('line')
    .attr('stroke', '#999')
    .attr('stroke-opacity', 0.6)
    .attr('stroke-width', d => Math.sqrt(d.value));

const node = gGraph.append('g')
.selectAll('circle')
.data(nodes)
.join('circle')
.attr('r', 5)
.attr('fill', '#000');

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

// update the links and nodes when data changes
function update(data) {
const links = data.map(d => ({ source: d.input, target: d.output }));

const nodeIDs = new Set(data.flatMap(d => [d.input, d.output]));

const nodes = Array.from(nodeIDs).map(id => ({ id }));

const link = gGraph.select('g')
.selectAll('line')
.data(links)
.join('line')
.attr('stroke', '#999')
.attr('stroke-opacity', 0.6)
.attr('stroke-width', d => Math.sqrt(d.value));

const node = gGraph.select('g')
.selectAll('circle')
.data(nodes)
.join('circle')
.attr('r', 5)
.attr('fill', '#000');

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

// example usage: update the visualization with new data
update(data);

const zoom = d3.zoom()
.scaleExtent([0.1, 10]) // установите диапазон масштабирования
.on('zoom', () => {
gGraph.attr('transform', d3.event.transform); // перемещаем и масштабируем только группу с графом
});

svg.call(zoom); // применяем зум к элементу SVG
</body>
    
</head>