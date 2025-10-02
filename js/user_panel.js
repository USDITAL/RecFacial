function applyFilters() {
    const date = document.getElementById('filterDate').value;
    const user = document.getElementById('filterUser').value;
    //console.log(`Filtrando por fecha: ${date} y usuario: ${user}`);
}

function exportToCSV() {
    const rows = document.querySelectorAll('table.records-table tr');
    let csvContent = "data:text/csv;charset=utf-8,";
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        let rowData = [];
        cells.forEach(cell => rowData.push(cell.textContent));
        csvContent += rowData.join(",") + "\n";
    });
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'registros.csv');
    document.body.appendChild(link);
    link.click();
}

function login() {
            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;
            if (user === 'admin' && pass === '1234') {
                document.querySelector('.login-form').style.display = 'none';
                document.querySelector('.records-section').style.display = 'block';
            } else {
                alert('Usuario o contraseña incorrectos');
            }
        }
