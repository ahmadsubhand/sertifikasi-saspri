var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

// // neroll page
// $("#add-row").on("click", function () {
//   var newRow = $(".f-row:first").clone();

//   newRow.find("input").val("");
//   newRow.find("select").val("");

//   $("#doc-row").append(newRow);
// });

// // Handle removing rows
// $(document).on("click", ".rem-row", function () {
//   console.log("pressed");
//   if ($(".f-row").length > 1) {
//     $(this).closest(".f-row").remove();
//   } else {
//     alert("At least one document is required.");
//   }
// });

