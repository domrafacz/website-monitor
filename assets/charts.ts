import ApexCharts from 'apexcharts';

interface ChartDataPoint {
    uptime: [number, number][];
    latency: [number, number][];
    period: string;
}

class WebsiteCharts {
    private uptimeChart: ApexCharts | null = null;
    private latencyChart: ApexCharts | null = null;
    private websiteId: number;
    private currentPeriod: string = '24h';
    private isLoading: boolean = false;

    constructor(websiteId: number) {
        this.websiteId = websiteId;
        this.initCharts();
        this.initPeriodTabs();
        this.loadData('24h');
    }

    private isDarkMode(): boolean {
        return document.documentElement.classList.contains('dark');
    }

    private getChartTheme() {
        const isDark = this.isDarkMode();
        return {
            mode: isDark ? 'dark' as const : 'light' as const,
            palette: 'palette1',
            monochrome: { enabled: false }
        };
    }

    private getCommonOptions(): ApexCharts.ApexOptions {
        const isDark = this.isDarkMode();
        return {
            chart: {
                height: 300,
                type: 'area',
                fontFamily: 'Inter, system-ui, sans-serif',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                background: 'transparent',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 500
                }
            },
            theme: this.getChartTheme(),
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            grid: {
                show: true,
                borderColor: isDark ? '#374151' : '#e5e7eb',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    style: {
                        colors: isDark ? '#9ca3af' : '#6b7280',
                        fontSize: '12px'
                    },
                    datetimeUTC: false
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                x: { format: 'dd MMM yyyy HH:mm' }
            },
            legend: {
                show: false
            }
        };
    }

    private initCharts(): void {
        const uptimeContainer = document.getElementById('uptime-chart');
        const latencyContainer = document.getElementById('latency-chart');

        if (!uptimeContainer || !latencyContainer) return;

        // Uptime Chart
        const uptimeOptions: ApexCharts.ApexOptions = {
            ...this.getCommonOptions(),
            chart: {
                ...this.getCommonOptions().chart,
                id: 'uptime-chart'
            },
            series: [{
                name: 'Uptime',
                data: []
            }],
            colors: ['#10b981'],
            yaxis: {
                min: 0,
                max: 100,
                labels: {
                    style: {
                        colors: this.isDarkMode() ? '#9ca3af' : '#6b7280',
                        fontSize: '12px'
                    },
                    formatter: (value: number) => `${value.toFixed(0)}%`
                }
            },
            tooltip: {
                ...this.getCommonOptions().tooltip,
                y: {
                    formatter: (value: number) => `${value.toFixed(2)}%`
                }
            }
        };

        this.uptimeChart = new ApexCharts(uptimeContainer, uptimeOptions);
        this.uptimeChart.render();

        // Latency Chart
        const latencyOptions: ApexCharts.ApexOptions = {
            ...this.getCommonOptions(),
            chart: {
                ...this.getCommonOptions().chart,
                id: 'latency-chart'
            },
            series: [{
                name: 'Latency',
                data: []
            }],
            colors: ['#6366f1'],
            yaxis: {
                min: 0,
                labels: {
                    style: {
                        colors: this.isDarkMode() ? '#9ca3af' : '#6b7280',
                        fontSize: '12px'
                    },
                    formatter: (value: number) => `${value.toFixed(0)} ms`
                }
            },
            tooltip: {
                ...this.getCommonOptions().tooltip,
                y: {
                    formatter: (value: number) => `${value.toFixed(0)} ms`
                }
            }
        };

        this.latencyChart = new ApexCharts(latencyContainer, latencyOptions);
        this.latencyChart.render();
    }

    private initPeriodTabs(): void {
        const tabs = document.querySelectorAll('[data-period]');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const period = (tab as HTMLElement).dataset.period;
                if (period && period !== this.currentPeriod && !this.isLoading) {
                    this.setActivePeriod(period);
                    this.loadData(period);
                }
            });
        });
    }

    private setActivePeriod(period: string): void {
        this.currentPeriod = period;
        const tabs = document.querySelectorAll('[data-period]');
        tabs.forEach(tab => {
            const tabPeriod = (tab as HTMLElement).dataset.period;
            if (tabPeriod === period) {
                tab.classList.remove('text-gray-500', 'dark:text-gray-400', 'bg-transparent');
                tab.classList.add('text-blue-600', 'dark:text-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30');
            } else {
                tab.classList.remove('text-blue-600', 'dark:text-blue-400', 'bg-blue-50', 'dark:bg-blue-900/30');
                tab.classList.add('text-gray-500', 'dark:text-gray-400', 'bg-transparent');
            }
        });
    }

    private showLoading(): void {
        this.isLoading = true;
        const loader = document.getElementById('charts-loader');
        if (loader) loader.classList.remove('hidden');
    }

    private hideLoading(): void {
        this.isLoading = false;
        const loader = document.getElementById('charts-loader');
        if (loader) loader.classList.add('hidden');
    }

    private async loadData(period: string): Promise<void> {
        this.showLoading();

        try {
            const response = await fetch(`/api/website/${this.websiteId}/chart-data?period=${period}`);
            if (!response.ok) throw new Error('Failed to fetch chart data');

            const data: ChartDataPoint = await response.json();

            const hasUptimeData = data.uptime && data.uptime.length > 0;
            const hasLatencyData = data.latency && data.latency.length > 0;

            // Handle uptime chart
            const uptimeNoData = document.getElementById('uptime-chart-no-data');
            const uptimeChart = document.getElementById('uptime-chart');
            if (uptimeNoData && uptimeChart) {
                if (hasUptimeData) {
                    uptimeNoData.classList.add('hidden');
                    uptimeChart.classList.remove('hidden');
                } else {
                    uptimeNoData.classList.remove('hidden');
                    uptimeChart.classList.add('hidden');
                }
            }

            // Handle latency chart
            const latencyNoData = document.getElementById('latency-chart-no-data');
            const latencyChart = document.getElementById('latency-chart');
            if (latencyNoData && latencyChart) {
                if (hasLatencyData) {
                    latencyNoData.classList.add('hidden');
                    latencyChart.classList.remove('hidden');
                } else {
                    latencyNoData.classList.remove('hidden');
                    latencyChart.classList.add('hidden');
                }
            }

            // Update charts with animation
            if (this.uptimeChart && hasUptimeData) {
                this.uptimeChart.updateSeries([{
                    name: 'Uptime',
                    data: data.uptime
                }]);
            }

            if (this.latencyChart && hasLatencyData) {
                this.latencyChart.updateSeries([{
                    name: 'Latency',
                    data: data.latency
                }]);
            }
        } catch (error) {
            console.error('Error loading chart data:', error);
        } finally {
            this.hideLoading();
        }
    }

    public updateTheme(): void {
        const isDark = this.isDarkMode();
        const newOptions: ApexCharts.ApexOptions = {
            theme: this.getChartTheme(),
            grid: {
                borderColor: isDark ? '#374151' : '#e5e7eb'
            },
            xaxis: {
                labels: {
                    style: {
                        colors: isDark ? '#9ca3af' : '#6b7280'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: isDark ? '#9ca3af' : '#6b7280'
                    }
                }
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light'
            }
        };

        if (this.uptimeChart) this.uptimeChart.updateOptions(newOptions);
        if (this.latencyChart) this.latencyChart.updateOptions(newOptions);
    }
}

// Initialize charts when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const chartContainer = document.getElementById('website-charts-container');
    if (chartContainer) {
        const websiteId = parseInt(chartContainer.dataset.websiteId || '0', 10);
        if (websiteId > 0) {
            const charts = new WebsiteCharts(websiteId);

            // Listen for theme changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        charts.updateTheme();
                    }
                });
            });

            observer.observe(document.documentElement, { attributes: true });
        }
    }
});
