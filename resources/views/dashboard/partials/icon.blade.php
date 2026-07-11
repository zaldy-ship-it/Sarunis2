@switch($name)
    @case('search')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.9" /><path d="M16 16L20 20" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" /></svg>
        @break
    @case('home')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11.5L12 5L20 11.5V20H14.5V14.5H9.5V20H4V11.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break
    @case('students')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.5 12C9.15685 12 10.5 10.6569 10.5 9C10.5 7.34315 9.15685 6 7.5 6C5.84315 6 4.5 7.34315 4.5 9C4.5 10.6569 5.84315 12 7.5 12Z" stroke="currentColor" stroke-width="1.8" /><path d="M16.5 10.5C17.7426 10.5 18.75 9.49264 18.75 8.25C18.75 7.00736 17.7426 6 16.5 6C15.2574 6 14.25 7.00736 14.25 8.25C14.25 9.49264 15.2574 10.5 16.5 10.5Z" stroke="currentColor" stroke-width="1.8" /><path d="M3.75 18.75C4.5792 16.5164 6.70334 15 9.25 15H9.75C12.2967 15 14.4208 16.5164 15.25 18.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M14.75 18.75C15.3267 17.3743 16.6869 16.5 18.25 16.5H18.5C19.4129 16.5 20.2565 16.7987 20.9375 17.3048" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('teacher')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5L20 8.5L12 12L4 8.5L12 5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M7 10.5V15.5C7 17.433 9.239 19 12 19C14.761 19 17 17.433 17 15.5V10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('class')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5" width="16" height="13" rx="2" stroke="currentColor" stroke-width="1.8" /><path d="M8 19.5H16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9 9H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9 12H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('subject')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 5.5H17.5C18.3284 5.5 19 6.17157 19 7V17.5H7.5C6.67157 17.5 6 16.8284 6 16V5.5Z" stroke="currentColor" stroke-width="1.8" /><path d="M6 8.5H19" stroke="currentColor" stroke-width="1.8" /><path d="M9 12H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('calendar')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5.5" width="16" height="14" rx="2.2" stroke="currentColor" stroke-width="1.8" /><path d="M8 3.75V7.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M16 3.75V7.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M4.5 10H19.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8 13H9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M12 13H13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M16 13H17.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8 16H9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M12 16H13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('recap')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8.5 5H7C5.89543 5 5 5.89543 5 7V18C5 19.1046 5.89543 20 7 20H17C18.1046 20 19 19.1046 19 18V7C19 5.89543 18.1046 5 17 5H15.5" stroke="currentColor" stroke-width="1.8" /><path d="M9 6.5C9 5.67157 9.67157 5 10.5 5H13.5C14.3284 5 15 5.67157 15 6.5V7.5H9V6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M8.75 12.25L10.5 14L14.75 9.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M9 17H15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('chart')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 19V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M5 19H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8 15.5L11.2 12.2L14 14.4L19 8.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M17 8.5H19V10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('attendance')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" stroke="currentColor" stroke-width="1.8" /><path d="M8 3.75V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M16 3.75V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8.5 12.5L10.5 14.5L15.5 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('report')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 18V10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M12 18V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M18 18V13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M4 18.5H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('schedule')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" stroke="currentColor" stroke-width="1.8" /><path d="M8 3.75V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M16 3.75V7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M12 10V14L14.5 15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('note')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 4.5H17L20 7.5V19.5H7C5.89543 19.5 5 18.6046 5 17.5V6.5C5 5.39543 5.89543 4.5 7 4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><path d="M9 10H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9 13.5H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('users')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 12C9.65685 12 11 10.6569 11 9C11 7.34315 9.65685 6 8 6C6.34315 6 5 7.34315 5 9C5 10.6569 6.34315 12 8 12Z" stroke="currentColor" stroke-width="1.8" /><path d="M16 12C17.6569 12 19 10.6569 19 9C19 7.34315 17.6569 6 16 6C14.3431 6 13 7.34315 13 9C13 10.6569 14.3431 12 16 12Z" stroke="currentColor" stroke-width="1.8" /><path d="M3.5 18.5C4.21205 16.4708 6.14653 15 8.375 15H8.625C10.8535 15 12.788 16.4708 13.5 18.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M10.5 18.5C11.212 16.4708 13.1465 15 15.375 15H15.625C17.8535 15 19.788 16.4708 20.5 18.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('settings')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8.75C10.2051 8.75 8.75 10.2051 8.75 12C8.75 13.7949 10.2051 15.25 12 15.25C13.7949 15.25 15.25 13.7949 15.25 12C15.25 10.2051 13.7949 8.75 12 8.75Z" stroke="currentColor" stroke-width="1.8" /><path d="M19 12C19 11.4304 18.9463 10.8734 18.8438 10.3333L20.5 9L18.5 5.5L16.5 6.25C15.6668 5.5809 14.6824 5.09371 13.6111 4.83333L13.25 2.75H10.75L10.3889 4.83333C9.31764 5.09371 8.33317 5.5809 7.5 6.25L5.5 5.5L3.5 9L5.15625 10.3333C5.05367 10.8734 5 11.4304 5 12C5 12.5696 5.05367 13.1266 5.15625 13.6667L3.5 15L5.5 18.5L7.5 17.75C8.33317 18.4191 9.31764 18.9063 10.3889 19.1667L10.75 21.25H13.25L13.6111 19.1667C14.6824 18.9063 15.6668 18.4191 16.5 17.75L18.5 18.5L20.5 15L18.8438 13.6667C18.9463 13.1266 19 12.5696 19 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" /></svg>
        @break
    @case('edit')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13.5 6.5L17.5 10.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M6 18L9.5 17.25L17.75 9C18.4384 8.31164 18.4384 7.19561 17.75 6.50725L17.4927 6.25C16.8044 5.56164 15.6884 5.56164 15 6.25L6.75 14.5L6 18Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break
    @case('eye')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.75 12C4.59773 8.6323 8.07611 6.5 12 6.5C15.9239 6.5 19.4023 8.6323 21.25 12C19.4023 15.3677 15.9239 17.5 12 17.5C8.07611 17.5 4.59773 15.3677 2.75 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.8" /></svg>
        @break
    @case('mail')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="6" width="16" height="12" rx="2" stroke="currentColor" stroke-width="1.8" /><path d="M5.5 8L12 13L18.5 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('phone')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7.6 5.5L9.8 9.2L8.25 10.74C9.2 12.76 10.89 14.44 12.91 15.39L14.45 13.85L18.15 16.05L17.2 19.03C16.99 19.69 16.35 20.1 15.67 20C9.87 19.16 5.04 14.33 4.2 8.53C4.1 7.85 4.51 7.21 5.17 7L7.6 5.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break
    @case('pin')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 20C12 20 18 14.5 18 10C18 6.68629 15.3137 4 12 4C8.68629 4 6 6.68629 6 10C6 14.5 12 20 12 20Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8" /></svg>
        @break
    @case('chevron-left')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14.5 6L8.5 12L14.5 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('chevron-right')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9.5 6L15.5 12L9.5 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('plus')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5V19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" /><path d="M5 12H19" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" /></svg>
        @break
    @case('filter')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 6H19.5L14 12.2V18.5L10 16.25V12.2L4.5 6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break
    @case('download')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4.5V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8.5 11.5L12 15L15.5 11.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M5 18.5H19" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('trash')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5.5 7.5H18.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M9 7.5V5.75C9 5.33579 9.33579 5 9.75 5H14.25C14.6642 5 15 5.33579 15 5.75V7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M8 10V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M12 10V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M16 10V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M6.5 7.5L7.2 18.2C7.25346 19.0179 7.93296 19.6538 8.7525 19.6538H15.2475C16.067 19.6538 16.7465 19.0179 16.8 18.2L17.5 7.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" /></svg>
        @break
    @case('chevron-down')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.5 9.5L12 15L17.5 9.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" /></svg>
        @break
    @case('logout')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 5H7C5.89543 5 5 5.89543 5 7V17C5 18.1046 5.89543 19 7 19H10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /><path d="M14 8L18 12L14 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M18 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" /></svg>
        @break
    @case('menu')
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 12H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M4 18H20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        @break
    @default
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" /></svg>
@endswitch
