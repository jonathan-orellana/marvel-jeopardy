// Sets Management with jQuery and AJAX
// Handles dynamic deletion, loading, and DOM updates without page reload

let pendingDeleteSetId = null;

// Use jQuery with document ready
$(document).ready(() => {
  const $modalOverlay = $("#deleteModalOverlay");
  const $modalMessage = $("#deleteModalText");
  const $confirmDeleteButton = $("#confirmDeleteBtn");
  const $cancelDeleteButton = $("#cancelDeleteBtn");

  // Open the delete confirmation modal for a given set
  // Stores the set id and shows the overlay with the set title
  const openDeleteModal = (setId, setTitle) => {
    pendingDeleteSetId = setId;
    $modalMessage.text(`Are you sure you want to delete "${setTitle}"?`);
    $modalOverlay.removeClass("hidden");
    // Add visual feedback - fade in effect
    $modalOverlay.fadeIn(200);
  };

  // Close the delete confirmation modal and reset the pending id
  const closeDeleteModal = () => {
    pendingDeleteSetId = null;
    $modalOverlay.fadeOut(200, () => {
      $modalOverlay.addClass("hidden");
    });
  };

  // Remove a set from the page by removing its list item
  // Also shows an empty-state message if no sets are left
  const removeSetFromPage = (setId) => {
    $(`.delete-set-btn[data-set-id="${setId}"]`)
      .closest("li")
      .fadeOut(300, function () {
        $(this).remove();
        // Check if no sets left, show empty message
        if ($("li.question-set-list").length === 0) {
          const $section = $("section");
          $section.append("<p>You don't have any sets yet. Click \"Create a new set\".</p>");
        }
      });
  };

  // Send an AJAX request to delete a set on the server
  // Returns the JSON response from the back-end
  const requestDeleteSet = async (setId) => {
    try {
      const response = await $.ajax({
        url: "index.php?command=delete_set",
        type: "POST",
        contentType: "application/json",
        data: JSON.stringify({ id: Number(setId) }),
        dataType: "json"
      });
      return response;
    } catch (error) {
      console.error("AJAX Error:", error);
      throw error;
    }
  };

  // Bind click handler to all delete buttons using event delegation
  const bindDeleteButtons = () => {
    $(document).on("click", ".delete-set-btn", function () {
      const setId = $(this).data("setId");
      const setTitle = $(this).data("setTitle");
      openDeleteModal(setId, setTitle);
    });
  };

  // Bind click handler to the cancel button in the modal
  const bindCancelButton = () => {
    $cancelDeleteButton.on("click", closeDeleteModal);
  };

  // Close the modal when clicking on the overlay background
  const bindOverlayClickToClose = () => {
    $modalOverlay.on("click", function (event) {
      if (event.target === this) {
        closeDeleteModal();
      }
    });
  };

  // Bind confirm delete button: send AJAX, handle response, update DOM
  const bindConfirmDeleteButton = () => {
    $confirmDeleteButton.on("click", async function () {
      if (!pendingDeleteSetId) return;

      // Disable button while processing
      $(this).prop("disabled", true).text("Deleting...");

      try {
        const result = await requestDeleteSet(pendingDeleteSetId);

        if (!result.ok) {
          const errorMsg = (result.errors && result.errors.join("\n")) || "Delete failed.";
          alert(errorMsg);
          $(this).prop("disabled", false).text("Yes, delete");
          return;
        }

        // Success - remove from page and close modal
        removeSetFromPage(pendingDeleteSetId);
        closeDeleteModal();
        $(this).prop("disabled", false).text("Yes, delete");

      } catch (error) {
        console.error("Delete error:", error);
        alert("Something went wrong deleting the set.");
        $(this).prop("disabled", false).text("Yes, delete");
      }
    });
  };

  // Initialize all event bindings
  bindDeleteButtons();
  bindCancelButton();
  bindOverlayClickToClose();
  bindConfirmDeleteButton();

  // Example: Anonymous function for logging page load
  (function () {
    console.log("Sets page loaded at:", new Date().toLocaleTimeString());
  })();
});
