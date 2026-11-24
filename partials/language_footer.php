<?php
/**
 * partials/language_footer.php – language menu at the end of every page (bottom right)
 *
 * Include it before </body>:  <?php require __DIR__ . '/partials/language_footer.php'; ?>
 * Needs bootstrap.php (session, csrf_token(), i18n).
 *
 * A change of the language is saved with ajax/i18n.php. Then:
 * - a page that defines window.onLanguageChanged(data) changes its texts in place,
 * - every other page reloads in the new language.
 */
?>
<style>
.lang-footer { display: flex; justify-content: flex-end; align-items: center; gap: 8px;
	box-sizing: border-box; width: 100%; max-width: 1240px; margin: 24px auto 0; padding: 12px 16px 20px;
	font-family: Arial, Helvetica, sans-serif; font-size: var(--font-text, 14px); color: #4A4431; clear: both; }
.lang-footer label { font-weight: 700; }
.lang-footer select { min-height: 44px; padding: 0 10px; border: 1px solid #A99E78; border-radius: 8px;
	background: #FFFDF6; color: #4A4431; font: inherit; cursor: pointer; }
</style>
<footer class = "lang-footer">
	<label for = "langSelect" data-i18n = "language"><?= e(t('language')) ?></label>
	<select id = "langSelect" data-csrf = "<?= e(csrf_token()) ?>">
		<?php foreach (i18n_languages() as $code => $langName): ?>
			<option value = "<?= e($code) ?>"<?= $code === current_lang() ? ' selected' : '' ?>><?= e($langName) ?></option>
		<?php endforeach; ?>
	</select>
</footer>
<script>
(function () {
	const select = document.getElementById("langSelect");
	if (!select) return;

	// Short page: move the footer down to the bottom edge of the window
	const footer = select.closest(".lang-footer");
	function placeFooter() {
		footer.style.marginTop = "24px";
		const bodyMargin = parseFloat(getComputedStyle(document.body).marginBottom) || 0;
		const footerBottom = footer.getBoundingClientRect().bottom + window.scrollY + bodyMargin;
		const gap = Math.floor(window.innerHeight - footerBottom);
		if (gap > 0) {
			footer.style.marginTop = (24 + gap) + "px";
		}
	}
	window.addEventListener("load", placeFooter);
	window.addEventListener("resize", placeFooter);
	if ("ResizeObserver" in window) {
		new ResizeObserver(placeFooter).observe(document.body);
	}
	placeFooter();
	let previous = select.value;
	select.addEventListener("change", function () {
		const body = new URLSearchParams({ csrf: select.dataset.csrf, lang: select.value });
		select.disabled = true;
		fetch("/ajax/i18n.php", { method: "POST", body, credentials: "same-origin" })
			.then(r => r.ok ? r.json() : Promise.reject(new Error("HTTP " + r.status)))
			.then(data => {
				previous = data.lang;
				if (typeof window.onLanguageChanged === "function") {
					window.onLanguageChanged(data); // change the texts in place
				} else {
					window.location.reload();       // page without in-place texts
				}
			})
			.catch(err => {
				console.error("Error:", err);
				select.value = previous; // keep the old language
			})
			.finally(() => { select.disabled = false; });
	});
})();
</script>
