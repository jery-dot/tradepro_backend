function toggleReadMore() {
  const moreText = document.getElementById("more-text");
  const btnLabel = document.getElementById("btn-label");

  if (moreText.style.display === "none") {
    moreText.style.display = "block";
    btnLabel.innerHTML = "READ LESS";
  } else {
    moreText.style.display = "none";
    btnLabel.innerHTML = "READ MORE";
  }
}
