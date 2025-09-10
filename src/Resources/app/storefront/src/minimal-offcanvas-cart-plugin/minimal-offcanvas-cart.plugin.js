import Plugin from 'src/plugin-system/plugin.class';

export default class MinimalOffCanvasCartPlugin extends Plugin {
    offCanvasLoaded = false;
    addtoCanvasFormIds = [];
    addItemUrl = '/checkout/line-item/add';
    init() {
        this.registerEvents();
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (
                        node.nodeType === 1 && 
                        node.classList.contains('offcanvas-minimal-main-content') 
                    ) {
                        const offcanvas = document.querySelector('.offcanvas');
                        //offcanvas.style.padding = "1%";
                        offcanvas.style.overflowY = "auto";
                        this.offCanvasAddToCartFormEvents();
                        this.offCanvasLoaded = true;
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    offCanvasAddToCartFormEvents() {
        const forms = document.querySelectorAll('.minimal-offcanvas-add-to-cart');
        // console.log(this.offCanvasLoaded, this.addtoCanvasFormIds)
        if (forms /*&&  !this.offCanvasLoaded*/) {
            for(let form of forms) {
                if (this.addtoCanvasFormIds.includes(form.id)) {
                  //  continue;
                }
                this.addtoCanvasFormIds.push(form.id);
                form.addEventListener('submit', async (event) => {
                    event.preventDefault(); 
                    const form = event.target;
                    if (form.dataset.submitting === "true") {
                        return; // already running
                    }
                    form.dataset.submitting = "true";
                    const formData = new FormData(form);
                    const addItemUrl = this.addItemUrl;

                    const offcanvas = document.querySelector('.offcanvas');

                    let loader = document.createElement('div');
                        loader.className = 'offcanvas-loading';
                        loader.style.cssText = `
                            text-align: center;
                            padding: 2rem;
                            position: fixed;
                            height: 100%;
                            width: 49%;
                            background: #cccccc3b;
                            z-index: 99999;
                        `;
                        loader.innerHTML = `
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Updating cart...</p>
                        `;

                    if (offcanvas) {
                        offcanvas.appendChild(loader)
                    }
                    
                    try {
                        const response = await fetch(addItemUrl, {
                            method : 'POST',
                            body   : formData,
                            headers : {
                                'X-Requested-With' : 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })

                        if (!response.ok) throw new Error("Failed add to cart");

                        const html = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        const newOffCanvas = doc.querySelector(".offcanvas-minimal-main-content");

                        if (offcanvas && newOffCanvas) {
                            offcanvas.innerHTML = newOffCanvas.outerHTML;
                            offcanvas.classList.add("show");
                            document.body.classList.add("offcanvas-open");
                        }

                    } catch (e) {
                        console.log('Error handling cart ', e)
                        if (offcanvas) {
                            offcanvas.innerHTML = `
                                <div class="offcanvas-error" style="
                                    text-align:center;
                                    padding: 2rem;
                                    color: red;">
                                    <p> Something went wrong </p>
                                </div>
                            `;
                        }
                    }  finally {
                        form.dataset.submitting = "false";
                        if (loader && loader.parentNode) {
                            loader.parentNode.removeChild(loader);
                        }
                    }

                    
                });
            }
        }
    }

    registerEvents() {
        document.addEventListener('click', (e) => {
            const clickedElement = e.target;
            const classes = [...clickedElement.classList];
            if (classes.includes('close-offcanvas')) {
                const offcanvasEl = document.querySelector('.offcanvas');
                const offcanvasBackdropEl = document.querySelector('.offcanvas-backdrop');
                if (offcanvasEl && offcanvasBackdropEl) {
                    offcanvasEl.classList.remove("show");
                    offcanvasBackdropEl.classList.remove("show");
                    this.offCanvasLoaded = false;
                    this.addtoCanvasFormIds = [];
                } 
            }
        });
    }

}
