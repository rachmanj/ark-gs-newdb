<div class="coal-ticker-container">
    <div class="coal-ticker">
        <div class="ticker-content">
            <span class="loading-text">Loading coal price and currency data...</span>
        </div>
    </div>
</div>

<style>
    .coal-ticker-container {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: rgba(52, 58, 64, 0.85);
        /* More transparent background */
        color: white;
        z-index: 1000;
        border-top: 1px solid #4b545c;
        padding: 5px 0;
        font-size: 14px;
        backdrop-filter: blur(3px);
        /* Add blur effect for modern browsers */
    }

    .coal-ticker {
        overflow: hidden;
        white-space: nowrap;
        box-sizing: border-box;
        width: 100%;
    }

    .ticker-content {
        display: inline-block;
        animation: ticker 60s linear infinite;
        padding-left: 100%;
    }

    .ticker-item {
        display: inline-block;
        padding: 0 15px;
    }

    .price-up {
        color: #28a745;
    }

    .price-down {
        color: #dc3545;
    }

    .price-neutral {
        color: #ffc107;
    }

    .data-source {
        color: #9e9e9e;
        font-size: 0.85em;
        font-style: italic;
        margin-left: 5px;
    }

    .currency-item {
        display: inline-block;
        padding: 0 15px;
    }

    .currency-rate {
        font-weight: bold;
    }

    @keyframes ticker {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    .coal-ticker-container:hover .ticker-content {
        animation-play-state: paused;
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .coal-ticker-container {
            font-size: 12px;
            padding: 3px 0;
        }

        .ticker-content {
            animation: ticker 20s linear infinite;
        }
    }

    @media (max-width: 480px) {
        .coal-ticker-container {
            font-size: 10px;
        }

        .ticker-content {
            animation: ticker 15s linear infinite;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetchCoalPrices();
        // Refresh data every 5 minutes
        setInterval(fetchCoalPrices, 5 * 60 * 1000);
    });

    function fetchCoalPrices() {
        axios.get('/api/coal-prices')
            .then(response => {
                const data = response.data;
                if (data.status === 'success') {
                    updateTickerContent(data.data);
                } else {
                    console.error('Failed to fetch coal prices:', data.message);
                }
            })
            .catch(error => {
                console.error('Error fetching coal prices:', error);
            });
    }

    function updateTickerContent(data) {
        const indonesiaData = data.indonesia;
        const newcastleData = data.newcastle;
        const exchangeRate = data.exchange_rate;

        const tickerContent = document.querySelector('.ticker-content');

        // Create ticker content
        let content = '';

        // Indonesia Coal Price
        const indonesiaChangeClass = getChangeClass(indonesiaData.change);
        content +=
            `<div class="ticker-item">Indonesia Coal Price Index: <strong>${indonesiaData.price} ${indonesiaData.unit}</strong> `;
        content +=
            `<span class="${indonesiaChangeClass}">(${indonesiaData.change >= 0 ? '+' : ''}${indonesiaData.change})</span> | `;
        content += `Last updated: ${formatDate(indonesiaData.date)}`;

        // Add source if available
        if (indonesiaData.source) {
            content += `<span class="data-source">(${indonesiaData.source})</span>`;
        }

        content += `</div>`;

        // Newcastle Coal Price
        const newcastleChangeClass = getChangeClass(newcastleData.change);
        content +=
            `<div class="ticker-item">Newcastle Coal Price Index: <strong>${newcastleData.price} ${newcastleData.unit}</strong> `;
        content +=
            `<span class="${newcastleChangeClass}">(${newcastleData.change >= 0 ? '+' : ''}${newcastleData.change})</span> | `;
        content += `Last updated: ${formatDate(newcastleData.date)}`;

        // Add source if available
        if (newcastleData.source) {
            content += `<span class="data-source">(${newcastleData.source})</span>`;
        }

        content += `</div>`;

        // Add USD/IDR exchange rate
        if (exchangeRate) {
            const formattedRate = exchangeRate.rate.toLocaleString('id-ID');
            const updateDateTime = formatDateTimeToWIB(exchangeRate.last_updated);
            content +=
                `<div class="currency-item">Current Exchange Rate: 1 USD = <span class="currency-rate">IDR ${formattedRate}</span> (Source: exchangerate-api.com) | Last Updated: ${updateDateTime} WIB</div>`;
        }

        tickerContent.innerHTML = content;

        // Restart animation
        tickerContent.style.animation = 'none';
        tickerContent.offsetHeight; // Trigger reflow

        // Set animation based on screen width
        const width = window.innerWidth;
        let duration = '60s';

        if (width <= 480) {
            duration = '30s';
        } else if (width <= 768) {
            duration = '45s';
        }

        tickerContent.style.animation = `ticker ${duration} linear infinite`;
    }

    function getChangeClass(change) {
        if (change > 0) return 'price-up';
        if (change < 0) return 'price-down';
        return 'price-neutral';
    }

    // Format date to "9 Jan 2025" format
    function formatDate(dateStr) {
        if (!dateStr) return '';

        const date = new Date(dateStr);
        const day = date.getDate();
        const month = date.toLocaleString('en-US', {
            month: 'short'
        });
        const year = date.getFullYear();

        return `${day} ${month} ${year}`;
    }

    // Format date and time to WIB
    function formatDateTimeToWIB(timestamp) {
        const date = new Date(timestamp * 1000);
        const options = {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        return date.toLocaleString('id-ID', options);
    }

    // Adjust animation speed when window is resized
    window.addEventListener('resize', function() {
        const tickerContent = document.querySelector('.ticker-content');
        if (!tickerContent) return;

        const width = window.innerWidth;
        let duration = '60s';

        if (width <= 480) {
            duration = '30s';
        } else if (width <= 768) {
            duration = '45s';
        }

        tickerContent.style.animation = 'none';
        tickerContent.offsetHeight; // Trigger reflow
        tickerContent.style.animation = `ticker ${duration} linear infinite`;
    });
</script>
