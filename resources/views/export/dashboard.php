<!-- Dashboard export: capture the original dashboard container and freeze Chart.js canvases in the clone. -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<style>
    #dashboard-export.is-exporting [data-dashboard-export-ignore] {
        display: none !important;
    }

    #dashboard-export.is-exporting *,
    #dashboard-export.is-exporting *::before,
    #dashboard-export.is-exporting *::after {
        animation: none !important;
        transition: none !important;
    }
</style>

<script>
    let dashboardExportInProgress = false;

    function waitForAnimationFrames() {
        return new Promise((resolve) => {
            requestAnimationFrame(() => requestAnimationFrame(resolve));
        });
    }

    async function waitForImages(container) {
        const images = Array.from(container.querySelectorAll('img'));

        await Promise.all(images.map((image) => {
            if (image.complete) {
                return Promise.resolve();
            }

            return new Promise((resolve) => {
                image.addEventListener('load', resolve, { once: true });
                image.addEventListener('error', resolve, { once: true });
            });
        }));
    }

    function updateDashboardCharts(container) {
        if (typeof Chart === 'undefined' || typeof Chart.getChart !== 'function') {
            return;
        }

        container.querySelectorAll('canvas').forEach((canvas) => {
            const chart = Chart.getChart(canvas);

            if (chart) {
                chart.resize();
                chart.update('none');
            }
        });
    }

    async function waitForDashboardReady(container) {
        if (document.fonts && document.fonts.ready) {
            await document.fonts.ready;
        }

        await waitForImages(container);

        window.dispatchEvent(new Event('resize'));
        await waitForAnimationFrames();

        updateDashboardCharts(container);
        await waitForAnimationFrames();

        await new Promise((resolve) => setTimeout(resolve, 500));
    }

    function createCanvasSnapshots(container) {
        return Array.from(container.querySelectorAll('canvas')).map((canvas) => {
            const bounds = canvas.getBoundingClientRect();

            return {
                dataUrl: canvas.toDataURL('image/png'),
                cssWidth: bounds.width,
                cssHeight: bounds.height,
                pixelWidth: canvas.width,
                pixelHeight: canvas.height
            };
        });
    }

    function replaceClonedCanvases(clonedDocument, snapshots) {
        const clonedDashboard = clonedDocument.getElementById('dashboard-export');

        if (!clonedDashboard) {
            return;
        }

        const clonedCanvases = Array.from(clonedDashboard.querySelectorAll('canvas'));

        clonedCanvases.forEach((canvas, index) => {
            const snapshot = snapshots[index];

            if (!snapshot || snapshot.cssWidth <= 0 || snapshot.cssHeight <= 0) {
                return;
            }

            const image = clonedDocument.createElement('img');
            image.src = snapshot.dataUrl;
            image.width = snapshot.pixelWidth;
            image.height = snapshot.pixelHeight;
            image.className = canvas.className;
            image.style.cssText = canvas.style.cssText;
            image.style.display = 'block';
            image.style.width = snapshot.cssWidth + 'px';
            image.style.height = snapshot.cssHeight + 'px';
            image.style.maxWidth = 'none';

            canvas.replaceWith(image);
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-dot-label]').forEach((label) => {
            label.style.position = 'relative';
            label.style.top = '-5px';
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-percent-badge]').forEach((badge) => {
            const value = badge.textContent.trim();
            const valueElement = clonedDocument.createElement('span');

            valueElement.textContent = value;
            valueElement.style.position = 'relative';
            valueElement.style.top = '-5px';
            valueElement.style.whiteSpace = 'nowrap';

            badge.textContent = '';
            badge.style.justifyContent = 'center';
            badge.appendChild(valueElement);
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-legend-item]').forEach((item) => {
            const dot = item.querySelector('span.rounded-full');

            if (dot) {
                dot.style.position = 'relative';
                dot.style.top = '6px';
            }
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-center-text]').forEach((element) => {
            const value = element.textContent.trim();
            const valueElement = clonedDocument.createElement('span');

            valueElement.textContent = value;
            valueElement.style.position = 'relative';
            valueElement.style.top = '-5px';
            valueElement.style.whiteSpace = 'nowrap';

            element.textContent = '';
            element.style.display = 'inline-flex';
            element.style.alignItems = 'center';
            element.style.justifyContent = 'center';
            element.appendChild(valueElement);
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-bar-legend-item]').forEach((item) => {
            const swatch = item.querySelector('span.rounded-sm');

            if (swatch) {
                swatch.style.position = 'relative';
                swatch.style.top = '6px';
            }
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-pavement-value]').forEach((value) => {
            value.style.position = 'relative';
            value.style.top = '-5px';
        });

        clonedDashboard.querySelectorAll('[data-dashboard-export-stability-value]').forEach((value) => {
            value.style.position = 'relative';
            value.style.top = '-5px';
        });
    }

    async function captureDashboard() {
        if (typeof html2canvas === 'undefined') {
            throw new Error('Library html2canvas gagal dimuat.');
        }

        const dashboard = document.getElementById('dashboard-export');

        if (!dashboard) {
            throw new Error('Wrapper dashboard export tidak ditemukan.');
        }

        dashboard.classList.add('is-exporting');

        try {
            await waitForDashboardReady(dashboard);

            const dashboardPage = dashboard.querySelector('#dashboard-page');
            const dashboardStyles = window.getComputedStyle(dashboard);
            const verticalPadding =
                parseFloat(dashboardStyles.paddingTop || 0) +
                parseFloat(dashboardStyles.paddingBottom || 0);
            const contentHeight = dashboardPage
                ? dashboardPage.getBoundingClientRect().height
                : dashboard.scrollHeight;
            const exportWidth = Math.ceil(dashboard.scrollWidth);
            const exportHeight = Math.ceil(contentHeight + verticalPadding);
            const canvasSnapshots = createCanvasSnapshots(dashboard);

            return await html2canvas(dashboard, {
                backgroundColor: '#f9fafb',
                scale: 2,
                useCORS: true,
                allowTaint: false,
                logging: false,
                imageTimeout: 15000,
                removeContainer: true,
                width: exportWidth,
                height: exportHeight,
                windowWidth: window.innerWidth,
                windowHeight: window.innerHeight,
                onclone: function (clonedDocument) {
                    replaceClonedCanvases(clonedDocument, canvasSnapshots);
                }
            });
        } finally {
            dashboard.classList.remove('is-exporting');
            window.dispatchEvent(new Event('resize'));
        }
    }

    function downloadDashboardImage(canvas, type, fileName) {
        const isJpeg = type === 'jpeg';
        const mimeType = isJpeg ? 'image/jpeg' : 'image/png';
        const extension = isJpeg ? 'jpg' : 'png';
        const dataUrl = canvas.toDataURL(mimeType, isJpeg ? 0.95 : 1);
        const link = document.createElement('a');

        link.download = fileName + '.' + extension;
        link.href = dataUrl;
        document.body.appendChild(link);
        link.click();
        link.remove();
    }

    function downloadDashboardPdf(canvas, fileName) {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            throw new Error('Library jsPDF gagal dimuat.');
        }

        const { jsPDF } = window.jspdf;
        const captureScale = 2;
        const pixelsPerMillimeter = 3.7795275591;
        const pageWidth = canvas.width / captureScale / pixelsPerMillimeter;
        const pageHeight = canvas.height / captureScale / pixelsPerMillimeter;
        const orientation = pageWidth > pageHeight ? 'landscape' : 'portrait';
        const pdf = new jsPDF({
            orientation: orientation,
            unit: 'mm',
            format: [pageWidth, pageHeight],
            compress: true
        });

        pdf.addImage(
            canvas.toDataURL('image/png'),
            'PNG',
            0,
            0,
            pageWidth,
            pageHeight,
            undefined,
            'FAST'
        );
        pdf.save(fileName + '.pdf');
    }

    async function exportDocument(type) {
        const allowedTypes = ['pdf', 'jpeg', 'png'];

        if (!allowedTypes.includes(type) || dashboardExportInProgress) {
            return;
        }

        dashboardExportInProgress = true;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyiapkan export...',
                text: 'Menunggu dashboard dan grafik selesai dirender.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        }

        try {
            const canvas = await captureDashboard();
            const date = new Date().toISOString().slice(0, 10);
            const fileName = 'dashboard-' + date;

            if (type === 'pdf') {
                downloadDashboardPdf(canvas, fileName);
            } else {
                downloadDashboardImage(canvas, type, fileName);
            }

            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Export berhasil',
                    text: 'File dashboard berhasil dibuat.',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Dashboard export failed:', error);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Export gagal',
                    text: error.message || 'Dashboard tidak dapat diekspor.'
                });
            }
        } finally {
            dashboardExportInProgress = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const url = new URL(window.location.href);
        const exportType = url.searchParams.get('export');

        if (!['pdf', 'jpeg', 'png'].includes(exportType)) {
            return;
        }

        url.searchParams.delete('export');
        history.replaceState({}, document.title, url.pathname + url.search + url.hash);

        setTimeout(() => exportDocument(exportType), 300);
    });
</script>
