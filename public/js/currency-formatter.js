// Function to format number as currency string (for display in input)
function formatRupiahInput(angka) {
    // Hapus semua karakter selain angka dan koma
    let number_string = angka.replace(/[^,\d]/g, "").toString();
    let split = number_string.split(",");
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
    }

    rupiah = split[1] !== undefined ? rupiah + "," + split[1] : rupiah;
    return rupiah;
}

function cleanAndParse(value) {
    return parseFloat(value.replace(/\./g, "").replace(/,/g, "."));
}

// Function to apply the formatting logic to an input element
function numberFormatted(inputId) {
    const inputElement = document.getElementById(inputId);

    if (inputElement) {
        // Apply formatting on input (as user types)
        inputElement.addEventListener("input", function (e) {
            e.target.value = formatRupiahInput(e.target.value);
        });

        // Re-format on blur (when input loses focus) to ensure consistency and strip extra chars
        inputElement.addEventListener("blur", function (e) {
            let value = e.target.value;
            if (value) {
                let numericValue = cleanAndParse(value);
                if (!isNaN(numericValue)) {
                    // Jika bilangan bulat, tampilkan tanpa desimal
                    if (numericValue % 1 === 0) {
                        e.target.value = formatRupiahInput(
                            String(parseInt(numericValue))
                        );
                    } else {
                        // Jika ada desimal, pastikan 2 angka di belakang koma (atau sesuai kebutuhan)
                        e.target.value = formatRupiahInput(
                            numericValue.toFixed(2).replace(/\./g, ",")
                        );
                    }
                } else {
                    e.target.value = ""; // Clear if invalid
                }
            }
        });

        // Initialize value on page load if old() value exists (for validation errors)
        if (inputElement.value) {
            let currentVal = cleanAndParse(inputElement.value);
            if (!isNaN(currentVal)) {
                if (currentVal % 1 === 0) {
                    inputElement.value = formatRupiahInput(
                        String(parseInt(currentVal))
                    );
                } else {
                    inputElement.value = formatRupiahInput(
                        currentVal.toFixed(2).replace(/\./g, ",")
                    );
                }
            }
        }
    }
}

function cleanFormatted(formId, inputIds) {
    const formElement = document.getElementById(formId);
    if (formElement) {
        formElement.addEventListener("submit", function () {
            inputIds.forEach((inputId) => {
                const input = document.getElementById(inputId);
                if (input) {
                    input.value = cleanAndParse(input.value);
                }
            });
        });
    }
}

function formatRupiahDisplayJS(angka) {
    let rupiah = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(angka);
    if (rupiah.endsWith(",00")) {
        rupiah = rupiah.slice(0, -3); // Hapus ',00'
    } else if (rupiah.endsWith(".00")) {
        rupiah = rupiah.slice(0, -3);
    }
    return rupiah;
}
