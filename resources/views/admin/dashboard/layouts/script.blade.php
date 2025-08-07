<script>
    document.addEventListener('DOMContentLoaded', () => {
        const valueByStore = @json($valueByStore);
        const inventoryValueCtx = document.getElementById('inventoryValueChart').getContext('2d');
        new Chart(inventoryValueCtx, {
            type: 'doughnut',
            data: {
                labels: valueByStore.map(store => store.name),
                datasets: [{
                    label: 'Giá trị tồn kho',
                    data: valueByStore.map(store => parseFloat(store.total_value)),
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(22, 163, 74, 0.8)',
                        'rgba(202, 138, 4, 0.8)',
                        'rgba(107, 114, 128, 0.8)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#4b5563',
                            font: {
                                family: "'Inter', sans-serif"
                            }
                        }
                    }
                }
            }
        });

        const topProducts = @json($topProducts);
        const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(topProductsCtx, {
            type: 'bar',
            data: {
                labels: topProducts.map(p => p.sku),
                datasets: [{
                    label: 'Số lượng',
                    data: topProducts.map(p => parseInt(p.total_quantity)),
                    backgroundColor: 'rgba(79, 70, 229, 0.6)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#4b5563'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#4b5563'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
