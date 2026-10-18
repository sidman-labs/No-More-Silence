(() => {
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  function showToast(message, type = "success") {
    let stack = $(".toast-stack");
    if (!stack) {
      stack = document.createElement("div");
      stack.className = "toast-stack";
      stack.setAttribute("aria-live", "polite");
      document.body.append(stack);
    }
    const toast = document.createElement("div");
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    stack.append(toast);
    window.setTimeout(() => toast.remove(), 3600);
  }

  const themeButton = $("[data-theme-toggle]");
  themeButton?.addEventListener("click", () => {
    const next = document.documentElement.dataset.theme === "light" ? "dark" : "light";
    document.documentElement.dataset.theme = next;
    try { localStorage.setItem("nms-theme", next); } catch (_) {}
  });

  const searchDialog = $("[data-search-dialog]");
  $("[data-search-open]")?.addEventListener("click", () => searchDialog?.showModal());
  $$("[data-search-close]").forEach((button) => button.addEventListener("click", () => searchDialog?.close()));
  searchDialog?.addEventListener("click", (event) => {
    if (event.target === searchDialog) searchDialog.close();
  });

  const menuButton = $("[data-mobile-menu]");
  const mobileMenu = $("[data-mobile-menu-panel]");
  menuButton?.addEventListener("click", () => {
    const open = menuButton.getAttribute("aria-expanded") === "true";
    menuButton.setAttribute("aria-expanded", String(!open));
    if (mobileMenu) mobileMenu.hidden = open;
  });

  const slider = $("[data-hero-slider]");
  if (slider) {
    const slides = $$("[data-slide]", slider);
    const dots = $("[data-slide-dots]", slider);
    let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains("is-current")));
    let timer;
    let startX = 0;
    const paint = () => {
      slides.forEach((slide, index) => {
        slide.classList.toggle("is-current", index === current);
        slide.setAttribute("aria-hidden", String(index !== current));
      });
      if (dots) {
        dots.replaceChildren(...slides.map((_, index) => {
          const button = document.createElement("button");
          button.type = "button";
          button.className = index === current ? "is-active" : "";
          button.setAttribute("aria-label", `কার্ড ${index + 1} দেখুন`);
          button.addEventListener("click", () => { current = index; paint(); reset(); });
          return button;
        }));
      }
    };
    const next = (delta) => {
      if (slides.length < 2) return;
      current = (current + delta + slides.length) % slides.length;
      paint();
    };
    const stop = () => window.clearInterval(timer);
    const reset = () => {
      stop();
      if (slides.length > 1) timer = window.setInterval(() => next(1), 4700);
    };
    $("[data-slide-prev]", slider)?.addEventListener("click", () => { next(-1); reset(); });
    $("[data-slide-next]", slider)?.addEventListener("click", () => { next(1); reset(); });
    slider.addEventListener("mouseenter", stop);
    slider.addEventListener("mouseleave", reset);
    slider.addEventListener("focusin", stop);
    slider.addEventListener("focusout", reset);
    slider.addEventListener("touchstart", (event) => { startX = event.changedTouches[0].screenX; stop(); }, { passive: true });
    slider.addEventListener("touchend", (event) => {
      const distance = event.changedTouches[0].screenX - startX;
      if (Math.abs(distance) > 45) next(distance < 0 ? 1 : -1);
      reset();
    }, { passive: true });
    paint();
    reset();
  }

  const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const revealItems = $$(".reveal");
  if ("IntersectionObserver" in window && !reducedMotion) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    revealItems.forEach((item) => revealObserver.observe(item));
  } else revealItems.forEach((item) => item.classList.add("is-visible"));

  const counters = $$("[data-count]");
  if (counters.length) {
    const animateCounter = (element) => {
      const target = Number(element.dataset.count || 0);
      if (reducedMotion || target === 0) {
        element.textContent = String(target);
        return;
      }
      const start = performance.now();
      const duration = 900;
      const frame = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        element.textContent = new Intl.NumberFormat("bn-BD").format(Math.round(target * (1 - (1 - progress) ** 3)));
        if (progress < 1) requestAnimationFrame(frame);
      };
      requestAnimationFrame(frame);
    };
    if ("IntersectionObserver" in window) {
      const countObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.4 });
      counters.forEach((counter) => countObserver.observe(counter));
    } else counters.forEach(animateCounter);
  }

  const reportForm = $("[data-report-form]");
  if (reportForm) {
    let step = 1;
    const steps = $$("[data-step]", reportForm);
    const fill = $("[data-progress-fill]");
    const labels = $$("[data-step-label]");
    const errorBoxes = $$("[data-error]", reportForm);
    const showStep = (next) => {
      step = next;
      steps.forEach((element) => element.classList.toggle("is-active", Number(element.dataset.step) === step));
      labels.forEach((label) => label.classList.toggle("is-current", Number(label.dataset.stepLabel) <= step));
      if (fill) fill.style.width = `${step * 100 / 3}%`;
      errorBoxes.forEach((box) => { box.textContent = ""; });
      reportForm.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
    };
    const validateStep = () => {
      const active = $(`[data-step="${step}"]`, reportForm);
      const controls = $$("[data-validate]", active);
      let message = "";
      for (const control of controls) {
        const value = control.value.trim();
        const type = control.dataset.validate;
        if (type === "required" && !value) {
          message = control.name === "category_id" ? "একটি ক্যাটাগরি বেছে নিন।" : "এই তথ্যটি পূরণ করুন।";
          break;
        }
        if (type === "description" && value.length < 30) {
          message = "ঘটনার বিবরণ কমপক্ষে ৩০ অক্ষরের লিখুন।";
          break;
        }
        if (value && type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          message = "ইমেইল ঠিকানাটি ঠিক করে লিখুন।";
          break;
        }
        if (value && type === "url") {
          try {
            const parsed = new URL(value);
            if (!["http:", "https:"].includes(parsed.protocol)) throw new Error();
          } catch (_) {
            message = "সম্পূর্ণ ও বৈধ লিংক দিন (https:// দিয়ে শুরু করুন)।";
            break;
          }
        }
      }
      if (step === 3 && !reportForm.elements.confirm_truth.checked) {
        message = "তথ্যের সত্যতা নিশ্চিত করতে সম্মতি দিন।";
      }
      const box = $("[data-error]", active);
      if (box) box.textContent = message;
      return !message;
    };
    $$("[data-next]", reportForm).forEach((button) => button.addEventListener("click", () => {
      if (validateStep()) showStep(Math.min(3, step + 1));
    }));
    $$("[data-back]", reportForm).forEach((button) => button.addEventListener("click", () => showStep(Math.max(1, step - 1))));
    reportForm.addEventListener("submit", (event) => {
      if (!validateStep()) {
        event.preventDefault();
        return;
      }
      const submit = $("[data-submit]", reportForm);
      if (submit) {
        submit.disabled = true;
        submit.textContent = "রিপোর্ট পাঠানো হচ্ছে…";
      }
    });

    const zone = $("[data-upload-zone]", reportForm);
    const fileInput = $("[data-upload-input]", reportForm);
    const previewBox = $("[data-upload-previews]", reportForm);
    const updatePreview = (files) => {
      if (!previewBox) return;
      previewBox.replaceChildren();
      if (files.length > 6) {
        if (fileInput) fileInput.value = "";
        showToast("সর্বোচ্চ ৬টি ছবি নির্বাচন করুন।", "error");
        return;
      }
      for (const file of files) {
        if (!["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024) {
          if (fileInput) fileInput.value = "";
          previewBox.replaceChildren();
          showToast("JPG, PNG বা WebP ছবি ৫ MB-এর মধ্যে হতে হবে।", "error");
          return;
        }
        const tile = document.createElement("div");
        tile.className = "upload-preview";
        const image = document.createElement("img");
        image.alt = "আপলোডের প্রিভিউ";
        image.src = URL.createObjectURL(file);
        image.onload = () => URL.revokeObjectURL(image.src);
        tile.append(image);
        previewBox.append(tile);
      }
    };
    fileInput?.addEventListener("change", () => updatePreview(Array.from(fileInput.files || [])));

    const photoInput = $("[data-photo-input]", reportForm);
    const photoPreview = $("[data-photo-preview]", reportForm);
    const updatePhotoPreview = () => {
      if (!photoPreview) return;
      photoPreview.replaceChildren();
      const file = photoInput?.files?.[0];
      if (!file) return;
      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024) {
        if (photoInput) photoInput.value = "";
        showToast("JPG, PNG বা WebP প্রোফাইল ছবি ৫ MB-এর মধ্যে হতে হবে।", "error");
        return;
      }
      const tile = document.createElement("div");
      tile.className = "upload-preview upload-preview-single";
      const image = document.createElement("img");
      image.alt = "প্রোফাইল ছবির প্রিভিউ";
      image.src = URL.createObjectURL(file);
      image.onload = () => URL.revokeObjectURL(image.src);
      tile.append(image);
      photoPreview.append(tile);
    };
    photoInput?.addEventListener("change", updatePhotoPreview);
    const photoZone = $("[data-photo-zone]", reportForm);
    if (photoZone) {
      photoZone.addEventListener("dragover", (event) => { event.preventDefault(); photoZone.classList.add("is-dragging"); });
      photoZone.addEventListener("dragleave", () => photoZone.classList.remove("is-dragging"));
      photoZone.addEventListener("drop", (event) => {
        event.preventDefault();
        photoZone.classList.remove("is-dragging");
        const file = event.dataTransfer?.files?.[0];
        if (photoInput && file) {
          try {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            photoInput.files = transfer.files;
          } catch (_) {}
        }
        updatePhotoPreview();
      });
    }
    zone?.addEventListener("dragover", (event) => { event.preventDefault(); zone.classList.add("is-dragging"); });
    zone?.addEventListener("dragleave", () => zone.classList.remove("is-dragging"));
    zone?.addEventListener("drop", (event) => {
      event.preventDefault();
      zone.classList.remove("is-dragging");
      const files = Array.from(event.dataTransfer?.files || []);
      if (fileInput && files.length) {
        try {
          const transfer = new DataTransfer();
          files.forEach((file) => transfer.items.add(file));
          fileInput.files = transfer.files;
        } catch (_) {}
      }
      updatePreview(files);
    });
  }

  $$("[data-copy-text], [data-copy-link]").forEach((button) => button.addEventListener("click", async () => {
    const value = button.dataset.copyText || new URL(button.dataset.copyLink, window.location.href).href;
    try {
      await navigator.clipboard.writeText(value);
      showToast(button.dataset.copyText?.startsWith("NMS-") ? "ট্র্যাকিং কোড কপি হয়েছে।" : "লিংক কপি হয়েছে।");
    } catch (_) {
      const input = document.createElement("textarea");
      input.value = value;
      input.style.position = "fixed";
      input.style.opacity = "0";
      document.body.append(input);
      input.select();
      document.execCommand("copy");
      input.remove();
      showToast("কপি করা হয়েছে।");
    }
  }));

  $$("[data-share-item]").forEach((button) => button.addEventListener("click", async () => {
    const url = button.dataset.shareUrl || window.location.href;
    const title = button.dataset.shareTitle || document.title;
    if (navigator.share) {
      try {
        await navigator.share({ title, text: "No More Silence — পর্যালোচিত কমিউনিটি রিপোর্ট", url });
        return;
      } catch (error) {
        if (error.name === "AbortError") return;
      }
    }
    try {
      await navigator.clipboard.writeText(url);
      showToast("শেয়ার করার লিংক কপি হয়েছে।");
    } catch (_) {
      const input = document.createElement("textarea");
      input.value = url;
      input.style.position = "fixed";
      input.style.opacity = "0";
      document.body.append(input);
      input.select();
      document.execCommand("copy");
      input.remove();
      showToast("শেয়ার করার লিংক কপি হয়েছে।");
    }
  }));

  const noticeDialog = $("[data-notice-dialog]");
  if (noticeDialog) {
    const version = noticeDialog.dataset.noticeVersion;
    const key = "nms-notice-dismissed";
    let dismissed = "";
    try { dismissed = localStorage.getItem(key) || ""; } catch (_) {}
    if (dismissed !== version) window.setTimeout(() => noticeDialog.showModal(), 900);
    $$("[data-notice-close]", noticeDialog).forEach((button) => button.addEventListener("click", () => {
      try { localStorage.setItem(key, version); } catch (_) {}
      noticeDialog.close();
    }));
    noticeDialog.addEventListener("click", (event) => {
      if (event.target === noticeDialog) {
        try { localStorage.setItem(key, version); } catch (_) {}
        noticeDialog.close();
      }
    });
  }

  const lightbox = $("[data-lightbox]");
  if (lightbox) {
    const lightboxImage = $("[data-lightbox-image]", lightbox);
    const lightboxCaption = $("[data-lightbox-caption-text]", lightbox);
    let lastTrigger = null;
    const closeLightbox = () => {
      if (lightbox.open) lightbox.close();
    };
    document.addEventListener("click", (event) => {
      const target = event.target instanceof Element ? event.target : null;
      const trigger = target?.closest("[data-lightbox-src]");
      if (trigger) {
        event.preventDefault();
        lastTrigger = trigger;
        const source = trigger.dataset.lightboxSrc || "";
        const caption = trigger.dataset.lightboxCaption || "রিপোর্টের প্রমাণ";
        if (lightboxImage) {
          lightboxImage.src = source;
          lightboxImage.alt = caption;
        }
        if (lightboxCaption) lightboxCaption.textContent = caption;
        if (typeof lightbox.showModal === "function") {
          lightbox.showModal();
          $("[data-lightbox-close]", lightbox)?.focus();
        } else {
          window.open(source, "_blank", "noopener");
        }
        return;
      }
      // A click on the dialog itself (outside the framed image) closes it.
      if (event.target === lightbox) closeLightbox();
    });
    $$("[data-lightbox-close]", lightbox).forEach((button) => button.addEventListener("click", closeLightbox));
    lightbox.addEventListener("close", () => lastTrigger?.focus());
  }

  const card = $("[data-card-capture]");

  // The poster is designed at 1200px wide. Every internal size is expressed in
  // --poster-u units so the live preview and the exported PNG share one scale.
  const refreshPosterScale = () => {
    if (!card) return;
    const width = card.getBoundingClientRect().width;
    if (width > 0) card.style.setProperty("--poster-u", `${width / 1200}px`);
  };
  if (card) {
    refreshPosterScale();
    window.addEventListener("resize", refreshPosterScale);
    if ("ResizeObserver" in window) new ResizeObserver(refreshPosterScale).observe(card);
  }

  if (card && window.QRCode) {
    const qr = $("[data-card-qr]");
    if (qr) new QRCode(qr, { text: window.location.href, width: 128, height: 128, correctLevel: QRCode.CorrectLevel.M });
  }

  async function renderCardPng() {
    if (!card) throw new Error("কার্ড পাওয়া যায়নি।");
    if (!window.html2canvas) throw new Error("কার্ড ডাউনলোড লাইব্রেরি লোড হয়নি। ইন্টারনেট সংযোগ যাচাই করুন।");
    await document.fonts.ready;
    // Make sure the cover, portrait and proof images are decoded before capture.
    await Promise.all(Array.from(card.querySelectorAll("img")).map((image) => (
      image.complete
        ? Promise.resolve()
        : new Promise((resolve) => {
            image.addEventListener("load", resolve, { once: true });
            image.addEventListener("error", resolve, { once: true });
          })
    )));
    const sizes = {
      facebook: { width: 1200, height: 630, file: "facebook-post" },
      square: { width: 1080, height: 1080, file: "square-post" },
      story: { width: 1080, height: 1920, file: "story" },
    };
    const format = sizes[card.dataset.cardSize] || sizes.facebook;
    const oldStyle = { width: card.style.width, height: card.style.height, aspectRatio: card.style.aspectRatio, flex: card.style.flex };
    card.classList.add("is-exporting");
    card.style.width = `${format.width}px`;
    card.style.height = `${format.height}px`;
    card.style.aspectRatio = `${format.width} / ${format.height}`;
    card.style.flex = "none";
    card.style.setProperty("--poster-u", `${format.width / 1200}px`);
    try {
      const canvas = await window.html2canvas(card, {
        backgroundColor: "#10131b",
        useCORS: true,
        // The design units are already tuned for the full 1200px layout, so a
        // 1:1 capture produces exactly the dimensions shown in the size picker.
        scale: 1,
        width: format.width,
        height: format.height,
        windowWidth: format.width,
        windowHeight: format.height,
        scrollX: 0,
        scrollY: 0,
        logging: false,
      });
      return { canvas, filename: `no-more-silence-${format.file}.png` };
    } finally {
      card.style.width = oldStyle.width;
      card.style.height = oldStyle.height;
      card.style.aspectRatio = oldStyle.aspectRatio;
      card.style.flex = oldStyle.flex;
      card.classList.remove("is-exporting");
      refreshPosterScale();
    }
  }

  const downloadButton = $("[data-download-card]");
  downloadButton?.addEventListener("click", async () => {
    const oldText = downloadButton.innerHTML;
    downloadButton.disabled = true;
    downloadButton.innerHTML = '<span class="mini-spinner"></span> কার্ড তৈরি হচ্ছে…';
    try {
      const { canvas, filename } = await renderCardPng();
      const link = document.createElement("a");
      link.download = filename;
      link.href = canvas.toDataURL("image/png");
      link.click();
      showToast("কার্ডটি ডাউনলোড হয়েছে।");
    } catch (error) {
      showToast(error.message || "কার্ড ডাউনলোড করা যায়নি।", "error");
    } finally {
      downloadButton.disabled = false;
      downloadButton.innerHTML = oldText;
    }
  });

  $("[data-share-card]")?.addEventListener("click", async () => {
    try {
      if (navigator.share && navigator.canShare) {
        const { canvas, filename } = await renderCardPng();
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, "image/png"));
        const file = new File([blob], filename, { type: "image/png" });
        if (navigator.canShare({ files: [file] })) {
          await navigator.share({ title: document.title, text: "No More Silence — পর্যালোচিত কমিউনিটি রিপোর্ট", files: [file] });
          return;
        }
      }
      await navigator.clipboard.writeText(window.location.href);
      showToast("শেয়ার করার লিংক কপি হয়েছে।");
    } catch (error) {
      if (error.name !== "AbortError") showToast(error.message || "শেয়ার করা যায়নি।", "error");
    }
  });

  $$("[data-size-select]").forEach((select) => select.addEventListener("change", () => {
    if (card) card.dataset.cardSize = select.value;
    refreshPosterScale();
  }));

  const themePicker = $("[data-theme-picker]");
  if (themePicker && card) {
    const themeButtons = $$("[data-card-theme]", themePicker);
    const themeKeys = themeButtons.map((button) => button.dataset.cardTheme).filter(Boolean);
    const storageKey = `nms-card-theme:${window.location.pathname}`;
    const applyTheme = (button) => {
      const key = button.dataset.cardTheme;
      if (!key) return;
      themeKeys.forEach((themeKey) => card.classList.remove(`theme-${themeKey}`));
      card.classList.add(`theme-${key}`);
      themeButtons.forEach((item) => {
        const active = item === button;
        item.classList.toggle("is-selected", active);
        item.setAttribute("aria-pressed", active ? "true" : "false");
      });
      try { localStorage.setItem(storageKey, key); } catch (_) {}
    };
    themePicker.addEventListener("click", (event) => {
      const button = event.target instanceof Element ? event.target.closest("[data-card-theme]") : null;
      if (button) applyTheme(button);
    });
    try {
      const savedTheme = localStorage.getItem(storageKey);
      const savedButton = savedTheme ? themeButtons.find((button) => button.dataset.cardTheme === savedTheme) : null;
      if (savedButton) applyTheme(savedButton);
    } catch (_) {}
  }

  $$(".toast").forEach((toast) => window.setTimeout(() => toast.remove(), 5200));
})();
