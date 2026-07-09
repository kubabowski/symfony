import { Controller } from '@hotwired/stimulus';
import Swiper from 'swiper';
import 'swiper/css';

export default class extends Controller {
    connect() {
        this.swiper = new Swiper(this.element, {
            spaceBetween: 32,
            autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
            speed: 600,
            pagination: { el: this.element.querySelector('.swiper-pagination'), clickable: true },
            navigation: {
                prevEl: this.element.querySelector('.swiper-button-prev'),
                nextEl: this.element.querySelector('.swiper-button-next'),
            },
            keyboard: { enabled: true },
            a11y: { enabled: true },
        });
    }

    disconnect() {
        this.swiper?.destroy();
    }
}