const energyTabs = [...document.querySelectorAll('[data-energy-tab]')];
const energyStatus = document.querySelector('[data-energy-status]');
const energyContent = document.querySelector('[data-energy-content]');
const dataAsOf = document.querySelector('[data-as-of]');
const summaryCount = document.querySelector('[data-summary-count]');
const summaryCountLabel = document.querySelector('[data-summary-count-label]');
const summaryTitle = document.querySelector('[data-summary-title]');
const summaryLowest = document.querySelector('[data-summary-lowest]');
const summaryLowestLabel = document.querySelector('[data-summary-lowest-label]');
const summaryCoverage = document.querySelector('[data-summary-coverage]');
const summaryCoverageLabel = document.querySelector('[data-summary-coverage-label]');
const chartTitle = document.querySelector('[data-chart-title]');
const chartLegend = document.querySelector('[data-chart-legend]');
const energyChart = document.querySelector('[data-energy-chart]');
const tableTitle = document.querySelector('[data-table-title]');
const tableHead = document.querySelector('[data-table-head]');
const tableBody = document.querySelector('[data-table-body]');
const tableSearch = document.querySelector('[data-table-search]');
const categoryFilter = document.querySelector('[data-category-filter]');
const tableEmpty = document.querySelector('[data-table-empty]');
const tableCount = document.querySelector('[data-table-count]');
const pageSummary = document.querySelector('[data-page-summary]');
const pageNumber = document.querySelector('[data-page-number]');
const pagePrevious = document.querySelector('[data-page-prev]');
const pageNext = document.querySelector('[data-page-next]');
const importNotice = document.querySelector('[data-import-notice]');
const importName = document.querySelector('[data-import-name]');
const workflowStatus = document.querySelector('[data-workflow-status]');
const templateButtons = document.querySelectorAll('[data-download-template]');
const pageSize = 10;
const energyCache = new Map();
let activeEnergyResource = 'tarif-listrik';
let apiRows = [];
let currentRows = [];
let currentMeta = {};
let currentPage = 1;
let sortKey = 'price';
let sortDirection = 1;
let activeController;

const isFuelData = () => activeEnergyResource === 'bbm';
const itemName = (item) => isFuelData() ? item.produk : item.golongan;
const itemCategory = (item) => isFuelData() ? (item.jenis || item.wilayah) : item.kategori;
const itemPrice = (item) => Number(isFuelData() ? item.harga : item.tarif_rp_per_kwh);
const itemPower = (item) => isFuelData() ? item.satuan : item.batas_daya;
const dash = '\u2014';

function formatRupiah(value) {
    const amount = Number(value);
    return Number.isFinite(amount)
        ? `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(amount)}`
        : dash;
}

function setEnergyStatus(message, hint = '') {
    energyContent.hidden = true;
    energyStatus.hidden = false;
    energyStatus.classList.add('is-error');
    energyStatus.replaceChildren();

    const messageElement = document.createElement('span');
    messageElement.className = 'energy-status-message';
    messageElement.textContent = message;
    energyStatus.append(messageElement);

    if (hint) {
        const hintElement = document.createElement('span');
        hintElement.className = 'energy-status-hint';
        hintElement.textContent = hint;
        energyStatus.append(hintElement);
    }

    const retryButton = document.createElement('button');
    retryButton.type = 'button';
    retryButton.className = 'button button-outline';
    retryButton.textContent = 'Coba lagi';
    retryButton.addEventListener('click', () => loadEnergyData(activeEnergyResource, true));
    energyStatus.append(retryButton);
}

function setLoadingState() {
    energyStatus.classList.remove('is-error');
    energyStatus.hidden = false;
    energyStatus.replaceChildren();
    const spinner = document.createElement('span');
    spinner.className = 'status-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    const message = document.createElement('span');
    message.textContent = 'Mengambil data dari API Indonesia...';
    energyStatus.append(spinner, message);
    energyContent.hidden = true;
}

function setWorkflowMessage(message, isError = false) {
    workflowStatus.textContent = message;
    workflowStatus.classList.toggle('is-error', isError);
    workflowStatus.hidden = false;
}

function resetWorkflowMessage() {
    workflowStatus.hidden = true;
    workflowStatus.textContent = '';
    workflowStatus.classList.remove('is-error');
}

function visibleRows() {
    const query = tableSearch.value.trim().toLocaleLowerCase('id');
    const category = categoryFilter.value;

    return currentRows
        .filter((item) => {
            const matchesQuery = query === '' || Object.values(item)
                .filter((value) => typeof value === 'string' || typeof value === 'number')
                .some((value) => String(value).toLocaleLowerCase('id').includes(query));
            const matchesCategory = category === '' || itemCategory(item) === category;

            return matchesQuery && matchesCategory;
        })
        .sort((left, right) => {
            const leftValue = sortKey === 'price' ? itemPrice(left)
                : sortKey === 'name' ? (itemName(left) || '')
                    : sortKey === 'category' ? (itemCategory(left) || '')
                        : (itemPower(left) || '');
            const rightValue = sortKey === 'price' ? itemPrice(right)
                : sortKey === 'name' ? (itemName(right) || '')
                    : sortKey === 'category' ? (itemCategory(right) || '')
                        : (itemPower(right) || '');
            const comparison = typeof leftValue === 'number'
                ? leftValue - rightValue
                : String(leftValue).localeCompare(String(rightValue), 'id', { numeric: true });

            return comparison * sortDirection;
        });
}

function renderTableHeaders() {
    const columns = [
        { key: 'name', label: isFuelData() ? 'PRODUK BBM' : 'GOLONGAN' },
        { key: 'power', label: isFuelData() ? 'SATUAN' : 'DAYA' },
        { key: 'price', label: isFuelData() ? 'HARGA JUAL' : 'TARIF / KWH' },
        { key: 'category', label: isFuelData() ? 'WILAYAH / JENIS' : 'KATEGORI' },
    ];
    const row = document.createElement('tr');

    columns.forEach(({ key, label }) => {
        const heading = document.createElement('th');
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.sortKey = key;
        button.setAttribute('aria-label', `Urutkan berdasarkan ${label.toLocaleLowerCase('id')}`);
        button.textContent = label;
        const indicator = document.createElement('span');
        indicator.className = 'sort-indicator';
        indicator.textContent = sortKey === key ? (sortDirection === 1 ? '↑' : '↓') : '↕';
        button.append(indicator);
        heading.append(button);
        row.append(heading);
    });

    tableHead.replaceChildren(row);
}

function renderChart(rows) {
    const namespace = 'http://www.w3.org/2000/svg';
    const width = 760;
    const height = 280;
    const left = 76;
    const right = 12;
    const top = 20;
    const bottom = 62;
    const chartWidth = width - left - right;
    const chartHeight = height - top - bottom;
    const values = rows
        .filter((item) => Number.isFinite(itemPrice(item)) && itemPrice(item) >= 0)
        .sort((a, b) => itemPrice(a) - itemPrice(b))
        .slice(0, 8);

    energyChart.replaceChildren();
    energyChart.setAttribute('aria-label', `${isFuelData() ? 'Perbandingan harga BBM bersubsidi' : 'Perbandingan tarif listrik PLN'} berdasarkan data API`);

    if (values.length === 0) {
        const emptyText = document.createElementNS(namespace, 'text');
        emptyText.setAttribute('x', String(width / 2));
        emptyText.setAttribute('y', String(height / 2));
        emptyText.setAttribute('text-anchor', 'middle');
        emptyText.setAttribute('class', 'chart-empty');
        emptyText.textContent = 'Belum ada nilai numerik untuk divisualisasikan.';
        energyChart.append(emptyText);
        return;
    }

    const maxValue = Math.max(...values.map(itemPrice), 1);
    const gradient = document.createElementNS(namespace, 'defs');
    const linearGradient = document.createElementNS(namespace, 'linearGradient');
    linearGradient.id = 'energyBarGradient';
    linearGradient.setAttribute('x1', '0');
    linearGradient.setAttribute('x2', '0');
    linearGradient.setAttribute('y1', '0');
    linearGradient.setAttribute('y2', '1');
    const start = document.createElementNS(namespace, 'stop');
    start.setAttribute('offset', '0%');
    start.setAttribute('stop-color', '#60C1E5');
    const end = document.createElementNS(namespace, 'stop');
    end.setAttribute('offset', '100%');
    end.setAttribute('stop-color', '#005A9C');
    linearGradient.append(start, end);
    gradient.append(linearGradient);
    energyChart.append(gradient);

    for (let index = 0; index <= 4; index += 1) {
        const y = top + (chartHeight / 4) * index;
        const line = document.createElementNS(namespace, 'line');
        line.setAttribute('x1', String(left));
        line.setAttribute('x2', String(width - right));
        line.setAttribute('y1', String(y));
        line.setAttribute('y2', String(y));
        line.setAttribute('class', 'chart-grid-line');
        energyChart.append(line);

        const tickLabel = document.createElementNS(namespace, 'text');
        tickLabel.setAttribute('x', String(left - 8));
        tickLabel.setAttribute('y', String(y + 3));
        tickLabel.setAttribute('text-anchor', 'end');
        tickLabel.setAttribute('class', 'chart-axis-value');
        tickLabel.textContent = formatRupiah(maxValue * (1 - index / 4));
        energyChart.append(tickLabel);
    }

    const verticalAxis = document.createElementNS(namespace, 'line');
    verticalAxis.setAttribute('x1', String(left));
    verticalAxis.setAttribute('x2', String(left));
    verticalAxis.setAttribute('y1', String(top));
    verticalAxis.setAttribute('y2', String(top + chartHeight));
    verticalAxis.setAttribute('class', 'chart-axis-line');
    energyChart.append(verticalAxis);

    const yAxisLabel = document.createElementNS(namespace, 'text');
    yAxisLabel.setAttribute('x', '13');
    yAxisLabel.setAttribute('y', String(top + chartHeight / 2));
    yAxisLabel.setAttribute('text-anchor', 'middle');
    yAxisLabel.setAttribute('class', 'chart-axis-title');
    yAxisLabel.setAttribute('transform', `rotate(-90 13 ${top + chartHeight / 2})`);
    yAxisLabel.textContent = isFuelData() ? 'Harga jual (Rp)' : 'Tarif (Rp/kWh)';
    energyChart.append(yAxisLabel);

    const slot = chartWidth / values.length;
    const barWidth = Math.min(42, slot * .48);

    values.forEach((item, index) => {
        const value = itemPrice(item);
        const barHeight = Math.max(2, (value / maxValue) * (chartHeight - 22));
        const x = left + slot * index + (slot - barWidth) / 2;
        const y = top + chartHeight - barHeight;
        const rect = document.createElementNS(namespace, 'rect');
        rect.setAttribute('x', String(x));
        rect.setAttribute('y', String(y));
        rect.setAttribute('width', String(barWidth));
        rect.setAttribute('height', String(barHeight));
        rect.setAttribute('class', 'chart-bar');
        rect.setAttribute('rx', '4');
        const title = document.createElementNS(namespace, 'title');
        title.textContent = `${itemName(item)}: ${formatRupiah(value)}`;
        rect.append(title);
        energyChart.append(rect);

        const valueLabel = document.createElementNS(namespace, 'text');
        valueLabel.setAttribute('x', String(x + barWidth / 2));
        valueLabel.setAttribute('y', String(y - 6));
        valueLabel.setAttribute('text-anchor', 'middle');
        valueLabel.setAttribute('class', 'chart-value-label');
        valueLabel.textContent = formatRupiah(value);
        energyChart.append(valueLabel);

        const label = document.createElementNS(namespace, 'text');
        label.setAttribute('x', String(x + barWidth / 2));
        label.setAttribute('y', String(top + chartHeight + 19));
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('class', 'chart-axis-label');
        label.textContent = (itemName(item) || '').slice(0, 9);
        energyChart.append(label);
    });

    const xAxisLabel = document.createElementNS(namespace, 'text');
    xAxisLabel.setAttribute('x', String(left + chartWidth / 2));
    xAxisLabel.setAttribute('y', String(height - 5));
    xAxisLabel.setAttribute('text-anchor', 'middle');
    xAxisLabel.setAttribute('class', 'chart-axis-title');
    xAxisLabel.textContent = isFuelData() ? 'Produk BBM' : 'Golongan tarif listrik';
    energyChart.append(xAxisLabel);
}

function renderSummary(rows) {
    const prices = rows.map(itemPrice).filter((value) => Number.isFinite(value) && value >= 0);
    const categories = new Set(rows.map(itemCategory).filter(Boolean));
    const minimum = prices.length > 0 ? Math.min(...prices) : null;

    summaryTitle.textContent = isFuelData() ? 'JENIS PRODUK' : 'GOLONGAN TARIF';
    summaryCount.textContent = new Intl.NumberFormat('id-ID').format(rows.length);
    summaryCountLabel.textContent = isFuelData() ? 'produk BBM pada dataset' : 'kelas tarif terdata';
    summaryLowest.textContent = minimum === null ? dash : formatRupiah(minimum);
    summaryLowest.classList.toggle('is-price', minimum !== null);
    summaryLowestLabel.textContent = isFuelData() ? 'harga jual terendah' : 'tarif terendah per kWh';
    summaryCoverage.textContent = new Intl.NumberFormat('id-ID').format(categories.size);
    summaryCoverageLabel.textContent = isFuelData() ? 'jenis / wilayah yang tercakup' : 'kategori pelanggan';
    chartTitle.textContent = isFuelData() ? 'Perbandingan harga BBM bersubsidi' : 'Perbandingan tarif listrik';
    chartLegend.textContent = isFuelData() ? 'Harga jual per satuan' : 'Tarif listrik per kWh';
    tableTitle.textContent = isFuelData() ? 'Harga BBM bersubsidi yang berlaku' : 'Tarif listrik yang berlaku';
}

function renderRows() {
    const rows = visibleRows();
    const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
    currentPage = Math.min(currentPage, pageCount);
    const first = (currentPage - 1) * pageSize;
    const pageRows = rows.slice(first, first + pageSize);

    renderTableHeaders();
    tableBody.replaceChildren();
    pageRows.forEach((item) => {
        const row = document.createElement('tr');
        const name = itemName(item) || dash;
        const nameCell = document.createElement('td');
        nameCell.textContent = name;
        if (item.dasar_hukum) nameCell.title = item.dasar_hukum;
        row.append(nameCell);

        const detailCell = document.createElement('td');
        detailCell.textContent = itemPower(item) || dash;
        row.append(detailCell);

        const priceCell = document.createElement('td');
        priceCell.className = 'price-cell';
        priceCell.textContent = formatRupiah(itemPrice(item));
        const unit = document.createElement('small');
        unit.textContent = isFuelData() ? (item.satuan || '') : 'per kWh';
        priceCell.append(unit);
        row.append(priceCell);

        const categoryCell = document.createElement('td');
        const categoryLabel = document.createElement('span');
        categoryLabel.className = `energy-category${item.subsidi ? ' is-subsidized' : ''}`;
        categoryLabel.textContent = itemCategory(item) || dash;
        categoryCell.append(categoryLabel);
        row.append(categoryCell);
        tableBody.append(row);
    });

    tableEmpty.hidden = rows.length > 0;
    tableCount.textContent = `${new Intl.NumberFormat('id-ID').format(rows.length)} baris`;
    pageSummary.textContent = rows.length === 0
        ? 'Tidak ada baris untuk ditampilkan'
        : `Menampilkan ${first + 1}–${Math.min(first + pageSize, rows.length)} dari ${rows.length} baris`;
    pageNumber.textContent = `${currentPage} / ${pageCount}`;
    pagePrevious.disabled = currentPage <= 1;
    pageNext.disabled = currentPage >= pageCount;
}

function renderFilters() {
    const previousValue = categoryFilter.value;
    const categories = [...new Set(currentRows.map(itemCategory).filter(Boolean))]
        .sort((left, right) => left.localeCompare(right, 'id'));
    categoryFilter.replaceChildren();
    const allOption = document.createElement('option');
    allOption.value = '';
    allOption.textContent = 'Semua kategori';
    categoryFilter.append(allOption);

    categories.forEach((category) => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categoryFilter.append(option);
    });

    categoryFilter.value = categories.includes(previousValue) ? previousValue : '';
}

function renderDashboard() {
    renderSummary(currentRows);
    renderFilters();
    renderRows();
    renderChart(visibleRows());
    const date = currentMeta.tanggal;
    dataAsOf.textContent = date ? `Data per ${date}` : 'Tanggal data tercantum dari API';
    energyContent.hidden = false;
    energyStatus.hidden = true;
}

async function loadEnergyData(resource, refresh = false) {
    activeEnergyResource = resource;
    activeController?.abort();
    activeController = new AbortController();
    const requestController = activeController;
    setLoadingState();

    if (!refresh && energyCache.has(resource)) {
        const cached = energyCache.get(resource);
        if (activeController === requestController && activeEnergyResource === resource) {
            apiRows = cached.data;
            currentRows = [...apiRows];
            currentMeta = cached.meta;
            importNotice.hidden = true;
            resetWorkflowMessage();
            currentPage = 1;
            renderDashboard();
        }
        return;
    }

    try {
        const response = await fetch(`/api/energi/${resource}`, {
            headers: { Accept: 'application/json' },
            signal: requestController.signal,
        });
        let payload;

        try {
            payload = await response.json();
        } catch {
            throw new Error('Respons API tidak dapat dibaca. Coba lagi nanti.');
        }

        if (activeController !== requestController || activeEnergyResource !== resource) return;

        if (!response.ok) {
            const message = payload.message || `Permintaan data gagal (HTTP ${response.status}).`;
            const hint = response.status === 503
                ? 'Tambahkan API_INDONESIA_API_KEY pada file .env untuk mengaktifkan koneksi.'
                : 'Periksa API key dan koneksi server, lalu coba lagi.';
            setEnergyStatus(message, hint);
            return;
        }

        if (payload.success !== true || !Array.isArray(payload.data)) {
            setEnergyStatus('Format data dari API Indonesia tidak sesuai.', 'Silakan coba lagi atau periksa dokumentasi API.');
            return;
        }

        energyCache.set(resource, { data: payload.data, meta: payload.meta || {} });
        apiRows = payload.data;
        currentRows = [...apiRows];
        currentMeta = payload.meta || {};
        importNotice.hidden = true;
        resetWorkflowMessage();
        currentPage = 1;
        renderDashboard();
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') return;
        if (activeController !== requestController) return;
        setEnergyStatus(
            error instanceof Error ? error.message : 'Tidak dapat terhubung ke API Indonesia.',
            'Periksa koneksi internet dan coba lagi.',
        );
    }
}

function updateRenderedData() {
    currentPage = 1;
    renderRows();
    renderChart(visibleRows());
}

function normalizeHeader(header) {
    return header
        .replace(/^\uFEFF/, '')
        .trim()
        .toLocaleLowerCase('id')
        .replace(/[^a-z0-9]/g, '');
}

function spreadsheetColumns() {
    return isFuelData()
        ? [
            { label: 'Produk', key: 'produk' },
            { label: 'Jenis', key: 'jenis' },
            { label: 'Harga', key: 'harga', type: 'price' },
            { label: 'Satuan', key: 'satuan' },
            { label: 'Wilayah', key: 'wilayah' },
            { label: 'Berlaku Mulai', key: 'berlaku_mulai' },
            { label: 'Dasar Hukum', key: 'dasar_hukum' },
        ]
        : [
            { label: 'Golongan', key: 'golongan' },
            { label: 'Daya', key: 'batas_daya' },
            { label: 'Kategori', key: 'kategori' },
            { label: 'Subsidi', key: 'subsidi', type: 'boolean' },
            { label: 'Tarif (Rp/kWh)', key: 'tarif_rp_per_kwh', type: 'price' },
            { label: 'Periode Mulai', key: 'periode_mulai' },
            { label: 'Dasar Hukum', key: 'dasar_hukum' },
        ];
}

function parseSpreadsheetNumber(value) {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : null;
    }

    const normalized = String(value ?? '')
        .trim()
        .replace(/\s/g, '')
        .replace(/[^\d,.-]/g, '')
        .replace(/\.(?=\d{3}(?:\D|$))/g, '')
        .replace(',', '.');
    const number = Number(normalized);
    return normalized !== '' && Number.isFinite(number) ? number : null;
}

function spreadsheetCellValue(cell) {
    let value = cell.value;

    if (value && typeof value === 'object') {
        if ('result' in value) {
            value = value.result;
        } else if (Array.isArray(value.richText)) {
            value = value.richText.map((part) => part.text).join('');
        } else if (typeof value.text === 'string') {
            value = value.text;
        } else {
            value = '';
        }
    }

    if (value instanceof Date) {
        return value.toISOString().slice(0, 10);
    }

    return value === null || value === undefined ? '' : String(value).trim();
}

function rowIdentity(record, columns) {
    return JSON.stringify(columns.map(({ key }) => {
        const value = record[key];
        return typeof value === 'string' ? value.trim().replace(/\s+/g, ' ').toLocaleLowerCase('id') : value;
    }));
}

function setWorksheetHeaders(worksheet, columns) {
    const header = worksheet.addRow(columns.map(({ label }) => label));
    header.font = { bold: true, color: { argb: 'FFFFFFFF' } };
    header.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1B365D' } };
    header.alignment = { vertical: 'middle' };
    header.height = 24;
    worksheet.views = [{ state: 'frozen', ySplit: 1 }];
    worksheet.autoFilter = {
        from: { row: 1, column: 1 },
        to: { row: 1, column: columns.length },
    };
    columns.forEach(({ label }, index) => {
        worksheet.getColumn(index + 1).width = Math.min(Math.max(label.length + 4, 16), 36);
    });
}

async function createWorkbook() {
    const { default: ExcelJS } = await import('exceljs');
    return new ExcelJS.Workbook();
}

function downloadWorkbook(workbook, filename) {
    return workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        });
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename;
        document.body.append(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
    });
}

async function exportExcel() {
    const rows = visibleRows();
    if (rows.length === 0) {
        setWorkflowMessage('Tidak ada data untuk diekspor.', true);
        return;
    }

    const fuel = isFuelData();
    const columns = spreadsheetColumns();
    try {
        const workbook = await createWorkbook();
        const worksheet = workbook.addWorksheet('Data');
        setWorksheetHeaders(worksheet, columns);

        rows.forEach((item) => {
            worksheet.addRow(columns.map(({ key }) => item[key] ?? ''));
        });

        const priceColumn = columns.findIndex(({ type }) => type === 'price') + 1;
        worksheet.getColumn(priceColumn).numFmt = '#,##0.##';
        await downloadWorkbook(
            workbook,
            `data-energi-${fuel ? 'bbm' : 'tarif-listrik'}-${new Date().toISOString().slice(0, 10)}.xlsx`,
        );
        setWorkflowMessage(`Ekspor Excel selesai: ${rows.length} baris.`);
    } catch (error) {
        setWorkflowMessage(error instanceof Error ? error.message : 'File Excel tidak dapat dibuat.', true);
    }
}

async function downloadExcelTemplate() {
    const fuel = isFuelData();
    const columns = spreadsheetColumns();
    try {
        const workbook = await createWorkbook();
        const worksheet = workbook.addWorksheet('Data');
        setWorksheetHeaders(worksheet, columns);

        const instructions = workbook.addWorksheet('Petunjuk');
        instructions.columns = [
            { header: 'Panduan impor Excel', key: 'topic', width: 28 },
            { header: 'Keterangan', key: 'description', width: 88 },
        ];
        instructions.addRows([
            {
                topic: 'Isi data',
                description: 'Masukkan data pada lembar Data, satu baris untuk setiap catatan. Jangan ubah judul kolom.',
            },
            {
                topic: 'Kolom wajib',
                description: fuel ? 'Produk dan Harga wajib diisi.' : 'Golongan dan Tarif (Rp/kWh) wajib diisi.',
            },
            {
                topic: 'Kolom opsional',
                description: columns.filter(({ key }) => key !== (fuel ? 'produk' : 'golongan')
                    && key !== (fuel ? 'harga' : 'tarif_rp_per_kwh'))
                    .map(({ label }) => label).join(', '),
            },
            {
                topic: 'Baris duplikat',
                description: 'Baris yang semua nilainya sama dengan baris sebelumnya akan dihapus otomatis saat impor.',
            },
            {
                topic: 'Privasi',
                description: 'File diproses di browser dan tidak diunggah ke server. Batas ukuran file 5 MB.',
            },
        ]);
        instructions.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
        instructions.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF1B365D' } };
        instructions.views = [{ state: 'frozen', ySplit: 1 }];

        await downloadWorkbook(
            workbook,
            `template-energi-${fuel ? 'bbm' : 'tarif-listrik'}.xlsx`,
        );
        setWorkflowMessage(`Template Excel ${fuel ? 'BBM' : 'tarif listrik'} berhasil diunduh.`);
    } catch (error) {
        setWorkflowMessage(error instanceof Error ? error.message : 'Template Excel tidak dapat dibuat.', true);
    }
}

async function importExcelFile(file) {
    resetWorkflowMessage();
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        setWorkflowMessage('File Excel melebihi batas ukuran 5 MB.', true);
        return;
    }
    if (!/\.xlsx$/i.test(file.name)) {
        setWorkflowMessage('Pilih file workbook Excel dengan format .xlsx.', true);
        return;
    }

    try {
        const workbook = await createWorkbook();
        await workbook.xlsx.load(await file.arrayBuffer());
        const worksheet = workbook.worksheets[0];
        if (!worksheet || worksheet.rowCount < 2) {
            throw new Error('Workbook harus berisi judul kolom dan setidaknya satu baris data pada lembar pertama.');
        }

        const columns = spreadsheetColumns();
        const headerRow = worksheet.getRow(1);
        const headers = Array.from({ length: headerRow.cellCount }, (_, index) => (
            normalizeHeader(spreadsheetCellValue(headerRow.getCell(index + 1)))
        ));
        const columnIndexes = columns.map(({ label }) => headers.indexOf(normalizeHeader(label)));
        const nameKey = isFuelData() ? 'produk' : 'golongan';
        const priceKey = isFuelData() ? 'harga' : 'tarif_rp_per_kwh';
        const nameIndex = columns.findIndex(({ key }) => key === nameKey);
        const priceIndex = columns.findIndex(({ key }) => key === priceKey);

        if (columnIndexes[nameIndex] < 0 || columnIndexes[priceIndex] < 0) {
            const required = isFuelData() ? 'Produk dan Harga' : 'Golongan dan Tarif (Rp/kWh)';
            throw new Error(`Kolom wajib ${required} tidak ditemukan. Unduh template Excel agar formatnya sesuai.`);
        }

        const records = [];
        let invalidCount = 0;
        worksheet.eachRow((row, rowNumber) => {
            if (rowNumber === 1) return;

            const record = {};
            columns.forEach(({ key, type }, index) => {
                const rawValue = columnIndexes[index] < 0
                    ? ''
                    : spreadsheetCellValue(row.getCell(columnIndexes[index] + 1));
                if (type === 'price') {
                    record[key] = parseSpreadsheetNumber(rawValue);
                } else if (type === 'boolean') {
                    record[key] = typeof rawValue === 'boolean'
                        ? rawValue
                        : ['true', '1', 'ya', 'yes'].includes(String(rawValue).trim().toLocaleLowerCase('id'));
                } else {
                    record[key] = rawValue;
                }
            });

            if (!record[nameKey] || record[priceKey] === null) {
                invalidCount += 1;
                return;
            }
            records.push(record);
        });

        if (records.length === 0) {
            throw new Error('Tidak ditemukan baris valid. Pastikan kolom nama dan harga diisi dengan benar.');
        }

        const seen = new Set();
        const uniqueRecords = records.filter((record) => {
            const identity = rowIdentity(record, columns);
            if (seen.has(identity)) return false;
            seen.add(identity);
            return true;
        });
        const duplicateCount = records.length - uniqueRecords.length;

        currentRows = uniqueRecords;
        currentPage = 1;
        importName.textContent = file.name;
        importNotice.hidden = false;
        renderDashboard();
        const skipped = invalidCount > 0 ? `, ${invalidCount} baris tidak valid dilewati` : '';
        setWorkflowMessage(`${uniqueRecords.length} baris unik dimuat dari Excel; ${duplicateCount} duplikat dihapus${skipped}.`);
    } catch (error) {
        setWorkflowMessage(error instanceof Error ? error.message : 'File Excel tidak dapat diolah.', true);
    }
}

function sanitizeNoteHtml(input) {
    const parser = new DOMParser();
    const parsed = parser.parseFromString(input, 'text/html');
    const allowedTags = new Set(['B', 'STRONG', 'I', 'EM', 'BR', 'P', 'UL', 'OL', 'LI']);

    function cleanNode(node) {
        if (node.nodeType === Node.TEXT_NODE) return document.createTextNode(node.textContent || '');
        if (node.nodeType !== Node.ELEMENT_NODE) return document.createDocumentFragment();

        const fragment = document.createDocumentFragment();
        [...node.childNodes].forEach((child) => fragment.append(cleanNode(child)));
        if (!allowedTags.has(node.tagName)) return fragment;

        const cleanElement = document.createElement(node.tagName.toLocaleLowerCase('en'));
        cleanElement.append(fragment);
        return cleanElement;
    }

    const safeHtml = document.createElement('div');
    [...parsed.body.childNodes].forEach((node) => safeHtml.append(cleanNode(node)));
    return safeHtml.innerHTML;
}

energyTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
        energyTabs.forEach((button) => {
            const isActive = button === tab;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', String(isActive));
        });
        tableSearch.value = '';
        categoryFilter.value = '';
        loadEnergyData(tab.dataset.energyTab);
    });
});

document.querySelector('[data-refresh]').addEventListener('click', () => {
    importNotice.hidden = true;
    loadEnergyData(activeEnergyResource, true);
});

tableSearch.addEventListener('input', updateRenderedData);
categoryFilter.addEventListener('change', updateRenderedData);
tableHead.addEventListener('click', (event) => {
    const button = event.target.closest('[data-sort-key]');
    if (!button) return;

    if (sortKey === button.dataset.sortKey) sortDirection *= -1;
    else {
        sortKey = button.dataset.sortKey;
        sortDirection = 1;
    }
    updateRenderedData();
});
pagePrevious.addEventListener('click', () => {
    currentPage = Math.max(1, currentPage - 1);
    renderRows();
});
pageNext.addEventListener('click', () => {
    currentPage += 1;
    renderRows();
});
document.querySelectorAll('[data-export-excel]').forEach((button) => button.addEventListener('click', exportExcel));
templateButtons.forEach((button) => button.addEventListener('click', downloadExcelTemplate));
document.querySelector('[data-print-report]').addEventListener('click', () => window.print());
document.querySelector('[data-pdf-report]').addEventListener('click', () => window.print());
document.querySelector('[data-import-trigger]').addEventListener('click', () => document.querySelector('[data-xlsx-file]').click());

const excelInputs = [
    document.querySelector('[data-xlsx-file]'),
    document.querySelector('[data-xlsx-drop]'),
];
excelInputs.forEach((input) => {
    input.addEventListener('change', () => {
        importExcelFile(input.files[0]);
        input.value = '';
    });
});

const excelDropZone = document.querySelector('[data-drop-zone="xlsx"]');
['dragenter', 'dragover'].forEach((eventName) => {
    excelDropZone.addEventListener(eventName, (event) => {
        event.preventDefault();
        excelDropZone.classList.add('is-dragging');
    });
});
['dragleave', 'drop'].forEach((eventName) => {
    excelDropZone.addEventListener(eventName, (event) => {
        event.preventDefault();
        excelDropZone.classList.remove('is-dragging');
    });
});
excelDropZone.addEventListener('drop', (event) => importExcelFile(event.dataTransfer.files[0]));
document.querySelector('[data-restore-api]').addEventListener('click', () => {
    currentRows = [...apiRows];
    importNotice.hidden = true;
    resetWorkflowMessage();
    currentPage = 1;
    renderDashboard();
});

let activePdfUrl;
const pdfInput = document.querySelector('[data-pdf-file]');
const pdfPreview = document.querySelector('[data-pdf-preview]');
const pdfFrame = document.querySelector('[data-pdf-frame]');

pdfInput.addEventListener('change', async () => {
    const file = pdfInput.files[0];
    pdfInput.value = '';
    if (!file) return;

    if (file.size > 15 * 1024 * 1024) {
        setWorkflowMessage('File PDF melebihi batas ukuran 15 MB.', true);
        return;
    }

    const header = new Uint8Array(await file.slice(0, 5).arrayBuffer());
    if (new TextDecoder().decode(header) !== '%PDF-') {
        setWorkflowMessage('File tidak dikenali sebagai PDF yang valid.', true);
        return;
    }

    if (activePdfUrl) URL.revokeObjectURL(activePdfUrl);
    activePdfUrl = URL.createObjectURL(file);
    pdfFrame.src = activePdfUrl;
    document.querySelector('[data-pdf-name]').textContent = file.name;
    pdfPreview.hidden = false;
    setWorkflowMessage('Pratinjau PDF dibuka secara lokal; file tidak diunggah.');
});

document.querySelector('[data-pdf-close]').addEventListener('click', () => {
    pdfPreview.hidden = true;
    pdfFrame.removeAttribute('src');
    if (activePdfUrl) URL.revokeObjectURL(activePdfUrl);
    activePdfUrl = null;
});

const noteEditor = document.querySelector('[data-note-editor]');
const noteState = document.querySelector('[data-note-state]');
const noteStorageKey = 'energi-desk-analysis-notes';

try {
    noteEditor.innerHTML = sanitizeNoteHtml(localStorage.getItem(noteStorageKey) || '');
} catch {
    noteState.textContent = 'Penyimpanan lokal tidak tersedia';
}

let noteSaveTimeout;
noteEditor.addEventListener('input', () => {
    noteState.textContent = 'Menyimpan...';
    window.clearTimeout(noteSaveTimeout);
    noteSaveTimeout = window.setTimeout(() => {
        try {
            const cleanHtml = sanitizeNoteHtml(noteEditor.innerHTML);
            localStorage.setItem(noteStorageKey, cleanHtml);
            noteState.textContent = 'Tersimpan di perangkat ini';
        } catch {
            noteState.textContent = 'Catatan tidak dapat disimpan';
        }
    }, 250);
});

noteEditor.addEventListener('paste', (event) => {
    event.preventDefault();
    const text = event.clipboardData.getData('text/plain');
    document.execCommand('insertText', false, text);
});

document.querySelectorAll('[data-format]').forEach((button) => {
    button.addEventListener('mousedown', (event) => event.preventDefault());
    button.addEventListener('click', () => {
        noteEditor.focus();
        document.execCommand(button.dataset.format, false);
        noteEditor.dispatchEvent(new Event('input'));
    });
});

const menuToggle = document.querySelector('[data-menu-toggle]');
const mainNav = document.querySelector('[data-main-nav]');
menuToggle.addEventListener('click', () => {
    const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
    menuToggle.setAttribute('aria-expanded', String(!isOpen));
    mainNav.classList.toggle('is-open', !isOpen);
});
mainNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
        menuToggle.setAttribute('aria-expanded', 'false');
        mainNav.classList.remove('is-open');
    });
});

loadEnergyData(activeEnergyResource);
