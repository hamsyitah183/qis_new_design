const exporters = [
    {
        id: 1,
        name: "Fresh Fruits Co. Ltd",
        country: "Thailand",
        address: "Bangkok Industrial Estate",
    },

    {
        id: 2,
        name: "Golden Palm Trading",
        country: "Indonesia",
        address: "Jakarta Selatan",
    },

    {
        id: 3,
        name: "Ocean Harvest Pte Ltd",
        country: "Singapore",
        address: "Jurong Port Road",
    },

    {
        id: 4,
        name: "China Food Export",
        country: "China",
        address: "Guangzhou",
    },
];

const searchInput = document.getElementById("exporterSearch");
const suggestionBox = document.getElementById("exporterSuggestion");

const countryInput = document.getElementById("exporterCountry");
const addressInput = document.getElementById("exporterAddress");
const exporterId = document.getElementById("exporterId");

function lockFields() {
    countryInput.readOnly = true;
    addressInput.readOnly = true;
}

function unlockFields() {
    countryInput.readOnly = false;
    addressInput.readOnly = false;
}

function clearSuggestion() {
    suggestionBox.innerHTML = "";
    suggestionBox.style.display = "none";
}

searchInput.addEventListener("input", function () {
    console.log('hi')
    const keyword = this.value.trim().toLowerCase();

    suggestionBox.innerHTML = "";

    if (keyword === "") {
        clearSuggestion();
        return;
    }

    const result = exporters.filter((exp) =>
        exp.name.toLowerCase().includes(keyword),
    );

    if (result.length) {
        result.forEach((exp) => {
            const item = document.createElement("div");

            item.className = "ipa-search-item";

            item.innerHTML = `
                <strong>${exp.name}</strong><br>
                <small>${exp.country}</small>
            `;

            item.onclick = function () {
                exporterId.value = exp.id;

                searchInput.value = exp.name;

                countryInput.value = exp.country;

                addressInput.value = exp.address;

                lockFields();

                clearSuggestion();
            };

            suggestionBox.appendChild(item);
        });
    } else {
        const add = document.createElement("div");

        add.className = "ipa-search-item ipa-add-new";

        add.innerHTML = `
            ➕ Add new exporter "<strong>${searchInput.value}</strong>"
        `;

        add.onclick = function () {
            exporterId.value = "";

            searchInput.value = searchInput.value;

            countryInput.value = "";
            addressInput.value = "";

            unlockFields();

            countryInput.focus();

            clearSuggestion();
        };

        suggestionBox.appendChild(add);
    }

    suggestionBox.style.display = "block";
});

document.addEventListener("click", function (e) {
    if (!e.target.closest(".ipa-search-wrapper")) {
        clearSuggestion();
    }
});
