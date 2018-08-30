import React, { Component } from "react";
import { connect } from 'react-redux';
import { history } from '../../store';

class ProgressBar extends Component {

  goToPath = path => {
    history.push(path);
  };

  render() {
    const { links } = this.props;
    return (
      <div class="progress-bar">
        <div class="progress-bar-wrapper">
          <ul class="progress-bar-items">
            {links
              ? links.map(link => (
                <li class="progress-bar-item" onClick={this.goToPath.bind(this, link.path)}>
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
  }
}
const mapStateToProps = () => ({
});

const mapDispatchToProps = {
}

export default connect(mapStateToProps, mapDispatchToProps)(ProgressBar);
