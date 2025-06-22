<div class="modal fade" id="wishlistModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="wishlistModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="wishlistModalLabel">Create new wishlist</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form class="modal-body needs-validation" novalidate="">
            <div class="mb-3">
              <label for="wl-name" class="form-label">Wishlist name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="wl-name" required="">
              <div class="invalid-feedback">Please enter the wishlist name!</div>
            </div>
            <div class="mb-3">
              <label for="wl-description" class="form-label">Description</label>
              <textarea class="form-control" id="wl-description" rows="4"></textarea>
            </div>
            <div class="mb-4">
              <label class="form-label">Privacy</label>
              <select class="form-select" data-select="{&quot;removeItemButton&quot;: false}" aria-label="Privacy settings">
                <option value="private">Private</option>
                <option value="public">Public</option>
                <option value="shared">Shared</option>
              </select>
            </div>
            <div class="d-flex gap-3">
              <button type="reset" class="btn btn-secondary w-100" data-bs-dismiss="modal" data-bs-target="#wishlistModal">Cancel</button>
              <button type="submit" class="btn btn-primary w-100">Create wishlist</button>
            </div>
          </form>
        </div>
      </div>
    </div>