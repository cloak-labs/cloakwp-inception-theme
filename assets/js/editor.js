const {
  registerBlockVariation,
  unregisterBlockVariation,
  registerBlockStyle,
  unregisterBlockStyle,
  getBlockVariations,
} = wp.blocks;
const { __ } = wp.i18n;

/**
 * Provide panel "labels" from the block editor's right sidebar that you wish to hide via JS (because some don't have specific CSS selectors)
 * Note: "Width settings" is for controlling `core/button` block widths (which we don't want to allow, but WP doesn't provide the option to turn off)
 */
const BLOCK_EDITOR_PANELS_TO_DISABLE = ["Width settings"];

/* REGISTER BLOCK STYLES + VARIATIONS
======================================= */

registerBlockStyle("core/button", [
  {
    // styled in theme.json > styles > elements > button
    name: "primary",
    label: __("Primary", "cloakwp/inception"),
    isDefault: true,
  },
  {
    // styled in editor.css
    name: "secondary",
    label: __("Secondary", "cloakwp/inception"),
  },
  {
    // styled in editor.css
    name: "outline",
    label: __("Outline", "cloakwp/inception"),
  },
]);

registerBlockStyle("core/image", [
  {
    // styled in editor.css
    name: "rounded-full",
    label: __("Round", "cloakwp/inception"),
  },
  {
    // styled in editor.css
    name: "no-shadow",
    label: __("No Shadow", "cloakwp/inception"),
  },
]);

wp.domReady(() => {
  hideBlockSettings();

  /* UNREGISTER BLOCK STYLES + VARIATIONS
  ======================================== */

  unregisterBlockStyle("core/button", ["fill"]);
  unregisterBlockStyle("core/image", ["rounded"]);

  // provide a list of embed blocks you wish to allow:
  const allowedEmbedBlocks = ["youtube"];

  getBlockVariations("core/embed").forEach((variation) => {
    if (!allowedEmbedBlocks.includes(variation.name)) {
      unregisterBlockVariation("core/embed", variation.name);
    }
  });

  /* Enables hiding Gutenberg settings panels via fancy JS, because they don't have specific CSS selectors we can target */
  async function hideBlockSettings() {
    MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

    // observe DOM changes within the Block Editor container, so we can run hideBlockSettingsCallback() to hide block settings we don't want (because WP doesn't provide a better option to disable certain settings)
    var observer = new MutationObserver(hideBlockSettingsMutationCallback);

    const gutenbergSidebar = await waitForElement(
      ".interface-interface-skeleton__sidebar"
    );

    if (gutenbergSidebar && gutenbergSidebar instanceof Node) {
      observer.observe(gutenbergSidebar, {
        attributes: true,
        subtree: true,
      });
    }

    function hideBlockSettingsMutationCallback(mutations, observer) {
      // fires when a DOM mutation occurs within Block Editor's sidebar (i.e. when you select a block etc.)
      const blockPanel = jQuery(
        ".interface-interface-skeleton__sidebar .components-panel"
      );
      if (!blockPanel) return;

      blockPanel.find("h2.components-panel__body-title").each(function () {
        // loop through the currently selected Block's metabox titles in right sidebar (the title strings are the only unique identifiers)
        const panelTitleElement = jQuery(this);
        const panelTitleString = panelTitleElement.find("button").text();

        if (BLOCK_EDITOR_PANELS_TO_DISABLE.includes(panelTitleString)) {
          panelTitleElement.closest(".components-panel__body").hide(); // hide the full metabox panel
        }
      });
    }
  }
});

/**
 * This function tries to find the element in the DOM matching the provided selector. If it
 * can't find the element, it waits for a specified interval (default 500ms) and tries again,
 * up to the maximum number of attempts. If the element isn't found after all attempts, the
 * function rejects the promise.
 *
 * You can adjust the interval parameter if you want to change how long it waits between retries.
 * Also, note that DOM querying and timeouts are inherently synchronous operations, so this function
 * returns a Promise, allowing you to use it in an asynchronous context.
 */
function waitForElement(
  selector,
  maxAttempts = 10,
  interval = 500,
  attempts = 0
) {
  return new Promise((resolve, reject) => {
    // Try to find the element using the selector
    const element = document.querySelector(selector);

    if (element) {
      // Element found, resolve the promise with the found element
      resolve(element);
    } else if (attempts < maxAttempts) {
      // Element not found, and still have attempts left
      // Wait for a bit and then try again
      setTimeout(() => {
        waitForElement(selector, maxAttempts, interval, attempts + 1)
          .then(resolve)
          .catch(reject);
      }, interval);
    } else {
      // Out of attempts, reject the promise
      reject(
        new Error(
          `Failed to find element with selector "${selector}" after ${maxAttempts} attempts`
        )
      );
    }
  });
}
