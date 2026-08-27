<div class="row">
  <div class="col-12 col-xl-6">
    <div class="card">
      <div class="card-header">
          <h4 class="">Active/Inactive users</h4>
      </div>
      <div class="card-body">
        <canvas id="userCountChart"></canvas>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-6">
  <div class="card">
      <div class="card-header">
          <h4 class="">New Users</h4>
      <div>
            <select class="form-control" id="periodSelect" onchange="userChartDataUpdate(this.value)">
                <option value="day">Last 7 Days</option>
                <option value="month">Last 6 Months</option>
                <option value="year" selected>Last 12 Months</option>
            </select>
        </div>
      </div>
      <div class="card-body">
        <canvas id="userChart"></canvas>
      </div>
    </div>
  </div>
</div>
</div>

@push('scripts')
<script>
    function updateChartData(chart,label,data) {
        chart.data.labels=label;
        chart.data.datasets[0].data=data;
        chart.update();
    }
    
    function userChartDataUpdate(selectedValue)
    {
         var postData = {
            '_token': CSRF_TOKEN, 
            'type': selectedValue  
        };
        $.ajax({
            url: "admin/get-chart-user" ,
            method: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                updateChartData(userChart,response.label,response.data);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }
    
    var userCountChart =false;
    var userChart=false;
    
  documentReady(function() {
      app.loadScript('https://cdn.jsdelivr.net/npm/chart.js',function(){
          // userCountChart start 
         userCountChart = new Chart(document.getElementById('userCountChart').getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
              data: [<?= $activeUser ?? 0 ?>, <?= $deactiveUser ?? 0 ?>],
              backgroundColor: ['#9C94F4', '#ff6384'],
            }],
          },
    
          options: {
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  usePointStyle: true,
                },
              },
            },
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', // Adjust the size of the hole in the center
            animation: {
              animateScale: true,
              animateRotate: true,
            },
    
            tooltips: {
              callbacks: {
                title: function(tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem[0].datasetIndex];
                  return dataset.label;
                },
                label: function(tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem.datasetIndex];
                  var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                    return previousValue + currentValue;
                  });
                  var currentValue = dataset.data[tooltipItem.index];
                  var percentage = ((currentValue / total) * 100).toFixed(2); // Rounded to 2 decimal places
    
                  return currentValue + " (" + percentage + "%)";
                },
              },
            },
          },
        });
        // userCountChart end 
        //userChart start
        userChart= new Chart(document.getElementById('userChart').getContext('2d'), {
            type: 'line',
            data:{
                labels:[],
                datasets: [{
                label: 'New Users',
                data:[],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
              }]
            }
        });
        //userChart end
        userChartDataUpdate('year');
      });
  });
</script>
@endpush