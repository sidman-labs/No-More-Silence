(() => {
  const root = document.querySelector(".home2");
  if (!root) return;

  const finishLoading = () => {
    root.querySelectorAll("[data-home2-loading]").forEach((region) => {
      region.removeAttribute("data-home2-loading");
      region.setAttribute("aria-busy", "false");
    });
  };

  requestAnimationFrame(() => requestAnimationFrame(finishLoading));
})();
