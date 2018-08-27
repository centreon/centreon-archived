import React from 'react';

const ProgressBar = ({links}) => {
  return (
    <div class="progress-bar">
      <div class="progress-bar-wrapper">
        <ul class="progress-bar-items">
          {links
            ? links.map(link => (
                <li class="progress-bar-item" onClick={link.path ? link.path : null}>
                  <span class={'progress-bar-link ' + (link.active ? 'active' : '') + (link.prevActive ? ' prev' : '')}>
                    {link.number}
                  </span>
                </li>
              ))
            : null}
        </ul>
      </div>
    </div>
  );
};

export default ProgressBar;
