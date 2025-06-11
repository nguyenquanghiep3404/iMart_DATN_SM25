
            // Khi người dùng click vào một ảnh
            card.addEventListener('click', () => {
                const fileData = { id: file.id, url: file.url, alt: file.alt_text };
                window.parent.postMessage({ action: 'fileSelected', file: fileData }, '*');
            });

            // Nếu cho phép chọn nhiều ảnh (cho gallery)
            // Bạn cần thêm nút "Xong" và gửi về một mảng file
            doneButton.addEventListener('click', () => {
                 const selectedFilesData = getSelectedFiles(); // Hàm này bạn tự viết
                 window.parent.postMessage({ action: 'fileSelected', files: selectedFilesData }, '*');
            });
