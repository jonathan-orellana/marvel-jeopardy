let pendingDeleteSetId = null;

document.addEventListener("DOMContentLoaded", function () {
  const modalOverlay = document.getElementById("deleteModalOverlay");
  const modalMessage = document.getElementById("deleteModalText");
  const confirmDeleteButton = document.getElementById("confirmDeleteBtn");
  const cancelDeleteButton = document.getElementById("cancelDeleteBtn");

  function openDeleteModal(setId, setTitle) {
    pendingDeleteSetId = setId;
    modalMessage.textContent = `Are you sure you want to delete "${setTitle}"?`;
    modalOverlay.classList.remove("hidden");
  }

  function closeDeleteModal() {
    pendingDeleteSetId = null;
    modalOverlay.classList.add("hidden");
  }

  function removeSetFromPage(setId) {
    const deleteButton = document.querySelector(
      `.delete-set-btn[data-set-id="${setId}"]`
    );
    if (!deleteButton) return;

    const listItem = deleteButton.closest("li");
    if (listItem) listItem.remove();
  }

  async function requestDeleteSet(setId) {
    const response = await fetch("index.php?command=delete_set", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id: Number(setId) })
    });

    const rawText = await response.text();

    try {
      return JSON.parse(rawText);
    } catch (e) {
      console.error("Non-JSON response:", rawText);
      throw new Error("Server didn't return JSON");
    }
  }

  function bindDeleteButtons() {
    document.querySelectorAll(".delete-set-btn").forEach(function (button) {
      button.addEventListener("click", function () {
        const setId = button.dataset.setId;
        const setTitle = button.dataset.setTitle;
        openDeleteModal(setId, setTitle);
      });
    });
  }

  function bindCancelButton() {
    cancelDeleteButton.addEventListener("click", function () {
      closeDeleteModal();
    });
  }

  function bindOverlayClickToClose() {
    modalOverlay.addEventListener("click", function (event) {
      if (event.target === modalOverlay) {
        closeDeleteModal();
      }
    });
  }

  function bindConfirmDeleteButton() {
    confirmDeleteButton.addEventListener("click", async function () {
      if (!pendingDeleteSetId) return;

      try {
        const result = await requestDeleteSet(pendingDeleteSetId);

        if (!result.ok) {
          alert((result.errors && result.errors.join("\n")) || "Delete failed.");
          return;
        }

        removeSetFromPage(pendingDeleteSetId);
        closeDeleteModal();

      } catch (error) {
        console.error(error);
        alert("Something went wrong deleting the set.");
      }
    });
  }

  bindDeleteButtons();
  bindCancelButton();
  bindOverlayClickToClose();
  bindConfirmDeleteButton();
});
