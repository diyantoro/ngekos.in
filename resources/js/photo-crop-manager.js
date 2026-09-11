// Photo Crop Manager - Crop multiple photos sebelum upload
export function photoCropManager() {
    return {
        files: [],
        currentIndex: -1,
        cropper: null,
        showModal: false,

        init() {
            this.$watch('showModal', value => {
                if (!value && this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            });
        },

        // Handle file selection
        onFilesSelected(event) {
            const selectedFiles = Array.from(event.target.files || []);
            if (selectedFiles.length === 0) return;

            // Add files with preview URLs
            this.files = selectedFiles.map((file, idx) => ({
                id: Date.now() + idx,
                file,
                previewUrl: URL.createObjectURL(file),
                cropped: false,
                croppedBlob: null
            }));

            // Start cropping first image
            if (this.files.length > 0) {
                this.currentIndex = 0;
                this.openCropModal();
            }
        },

        // Open crop modal for current image
        openCropModal() {
            if (this.currentIndex < 0 || this.currentIndex >= this.files.length) return;
            this.showModal = true;
            this.$nextTick(() => {
                this.initCropper();
            });
        },

        // Initialize Cropper.js
        initCropper() {
            if (this.cropper) {
                this.cropper.destroy();
            }

            const img = this.$refs.cropImage;
            if (!img) return;

            const currentFile = this.files[this.currentIndex];
            img.onload = () => {
                this.cropper = new Cropper(img, {
                    viewMode: 1,
                    aspectRatio: NaN, // Free aspect ratio
                    autoCropArea: 0.95,
                    responsive: true,
                    background: false,
                    modal: true,
                    guides: true,
                    center: true,
                    highlight: true,
                    cropBoxResizable: true,
                    cropBoxMovable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            img.src = currentFile.previewUrl;
        },

        // Apply crop to current image
        applyCrop() {
            if (!this.cropper) return;

            const canvas = this.cropper.getCroppedCanvas({
                maxWidth: 1920,
                maxHeight: 1920,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            canvas.toBlob((blob) => {
                if (!blob) return;

                const currentFile = this.files[this.currentIndex];
                currentFile.cropped = true;
                currentFile.croppedBlob = blob;

                // Move to next image or finish
                this.nextImage();
            }, 'image/jpeg', 0.92);
        },

        // Skip crop for current image
        skipCrop() {
            const currentFile = this.files[this.currentIndex];
            currentFile.cropped = true;
            currentFile.croppedBlob = currentFile.file; // Use original
            this.nextImage();
        },

        // Move to next image or finish
        nextImage() {
            this.currentIndex++;

            if (this.currentIndex >= this.files.length) {
                // All done
                this.showModal = false;
                this.uploadAllFiles();
                return;
            }

            // Crop next image
            this.$nextTick(() => {
                this.initCropper();
            });
        },

        // Go to previous image
        prevImage() {
            if (this.currentIndex <= 0) return;
            this.currentIndex--;
            this.$nextTick(() => {
                this.initCropper();
            });
        },

        // Cancel all
        cancelAll() {
            this.files.forEach(f => {
                if (f.previewUrl) URL.revokeObjectURL(f.previewUrl);
            });
            this.files = [];
            this.currentIndex = -1;
            this.showModal = false;
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }

            // Reset file input
            const input = this.$refs.fileInput;
            if (input) input.value = '';
        },

        // Upload all cropped files to Livewire - SEQUENTIAL UPLOAD
        async uploadAllFiles() {
            const wire = this.$wire;
            if (!wire) {
                console.error('Livewire wire not found');
                this.cancelAll();
                return;
            }

            try {
                // Upload one by one and collect uploaded files
                for (let i = 0; i < this.files.length; i++) {
                    const f = this.files[i];
                    if (!f.cropped || !f.croppedBlob) continue;

                    const fileName = `foto-${Date.now()}-${i}.jpg`;
                    const file = new File([f.croppedBlob], fileName, { type: 'image/jpeg' });

                    // Upload to Livewire temporary storage
                    await new Promise((resolve, reject) => {
                        wire.upload('galeriBaru', file,
                            () => {
                                resolve();
                            },
                            (error) => reject(error)
                        );
                    });
                }

                // Cleanup
                this.files.forEach(f => {
                    if (f.previewUrl) URL.revokeObjectURL(f.previewUrl);
                });
                this.files = [];
                this.currentIndex = -1;

                // Reset input
                const input = this.$refs.fileInput;
                if (input) input.value = '';

            } catch (error) {
                console.error('Upload failed:', error);
                alert('Upload gagal. Silakan coba lagi.');
                this.cancelAll();
            }
        },

        // Check if can go back
        get canGoPrev() {
            return this.currentIndex > 0;
        },

        // Check if this is last image
        get isLastImage() {
            return this.currentIndex === this.files.length - 1;
        },
    };
}
