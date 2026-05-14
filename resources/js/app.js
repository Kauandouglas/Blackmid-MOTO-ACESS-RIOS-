import './bootstrap';

function cardsPerView() {
	if (window.innerWidth >= 1024) return 4;
	return 2;
}

function initFeaturedSlider() {
	const sliders = document.querySelectorAll('[data-featured-slider]');

	sliders.forEach((slider) => {
		const track = slider.querySelector('[data-slider-track]');
		const slides = Array.from(track?.children ?? []);
		const prevButtons = slider.querySelectorAll('[data-slider-prev]');
		const nextButtons = slider.querySelectorAll('[data-slider-next]');

		if (!track || slides.length === 0) return;

		let index = 0;
		let perView = cardsPerView();
		let intervalId;
		let isDragging = false;
		let dragStartX = 0;
		let dragDeltaX = 0;

		const maxIndex = () => Math.max(0, slides.length - perView);

		const updateControls = () => {
			const disabled = slides.length <= perView;

			prevButtons.forEach((button) => {
				button.disabled = disabled;
				button.classList.toggle('opacity-40', disabled);
				button.classList.toggle('cursor-not-allowed', disabled);
			});

			nextButtons.forEach((button) => {
				button.disabled = disabled;
				button.classList.toggle('opacity-40', disabled);
				button.classList.toggle('cursor-not-allowed', disabled);
			});
		};

		const render = () => {
			index = Math.min(index, maxIndex());
			track.style.transform = `translateX(-${(index * 100) / perView}%)`;
			updateControls();
		};

		const next = () => {
			index = index >= maxIndex() ? 0 : index + 1;
			render();
		};

		const prev = () => {
			index = index <= 0 ? maxIndex() : index - 1;
			render();
		};

		const startAutoplay = () => {
			clearInterval(intervalId);
			intervalId = setInterval(next, 4500);
		};

		const stopAutoplay = () => {
			clearInterval(intervalId);
		};

		const dragThreshold = () => Math.max(30, slider.clientWidth * 0.06);

		const startDrag = (clientX) => {
			isDragging = true;
			dragStartX = clientX;
			dragDeltaX = 0;
			stopAutoplay();
			track.classList.add('cursor-grabbing');
			track.style.transitionDuration = '0ms';
		};

		const moveDrag = (clientX) => {
			if (!isDragging) return;

			dragDeltaX = clientX - dragStartX;
			const baseOffsetPercent = -((index * 100) / perView);
			const dragOffsetPercent = (dragDeltaX / slider.clientWidth) * 100;
			track.style.transform = `translateX(${baseOffsetPercent + dragOffsetPercent}%)`;
		};

		const endDrag = () => {
			if (!isDragging) return;

			isDragging = false;
			track.classList.remove('cursor-grabbing');
			track.style.transitionDuration = '';

			if (Math.abs(dragDeltaX) > dragThreshold()) {
				if (dragDeltaX < 0) next();
				if (dragDeltaX > 0) prev();
			} else {
				render();
			}

			dragDeltaX = 0;
			startAutoplay();
		};

		prevButtons.forEach((button) => button.addEventListener('click', prev));
		nextButtons.forEach((button) => button.addEventListener('click', next));

		slider.addEventListener('mouseenter', stopAutoplay);
		slider.addEventListener('mouseleave', startAutoplay);

		track.classList.add('cursor-grab');
		track.addEventListener('mousedown', (event) => {
			startDrag(event.clientX);
		});
		window.addEventListener('mousemove', (event) => {
			moveDrag(event.clientX);
		});
		window.addEventListener('mouseup', endDrag);
		track.addEventListener('mouseleave', endDrag);

		track.addEventListener('touchstart', (event) => {
			if (!event.touches[0]) return;
			startDrag(event.touches[0].clientX);
		}, { passive: true });
		track.addEventListener('touchmove', (event) => {
			if (!event.touches[0]) return;
			moveDrag(event.touches[0].clientX);
		}, { passive: true });
		track.addEventListener('touchend', endDrag);
		track.addEventListener('touchcancel', endDrag);

		window.addEventListener('resize', () => {
			perView = cardsPerView();
			render();
		});

		render();
		startAutoplay();
	});
}

document.addEventListener('DOMContentLoaded', initFeaturedSlider);
