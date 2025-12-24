// DATA PRODUK (contoh, boleh diganti)
const products = [
    { name: "Pulpen", price: 5000 },
    { name: "Buku Tulis", price: 15000 },
    { name: "Tas Sekolah", price: 120000 },
    { name: "Penggaris", price: 7000 },
    { name: "Laptop", price: 6500000 },
];

// Render awal
renderTable(products);

// Fungsi render tabel
function renderTable(list) {
    const tbody = document.querySelector("#productTable tbody");
    tbody.innerHTML = "";

    list.forEach(item => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.price.toLocaleString()}</td>
        `;
        tbody.appendChild(row);
    });
}

// EVENT LISTENER FILTER
document.getElementById("filterName").addEventListener("input", applyFilter);
document.getElementById("filterPrice").addEventListener("change", applyFilter);

// Fungsi FILTER
function applyFilter() {
    const nameVal = document.getElementById("filterName").value.toLowerCase();
    const priceVal = document.getElementById("filterPrice").value;

    let filtered = products.filter(item => {
        let matchName = item.name.toLowerCase().includes(nameVal);
        let matchPrice = true;

        if (priceVal === "low")      matchPrice = item.price < 50000;
        if (priceVal === "mid")      matchPrice = item.price >= 50000 && item.price <= 100000;
        if (priceVal === "high")     matchPrice = item.price > 100000;

        return matchName && matchPrice;
    });

    renderTable(filtered);
}
