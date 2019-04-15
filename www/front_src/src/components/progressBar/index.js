import React, { Component } from "react";
import { connect } from "react-redux";
import { history } from "../../store";
import classnames from 'classnames';
import styles from './progressbar.scss';

class ProgressBar extends Component {
  goToPath = path => {
    history.push(path);
  };

  render() {
    const { links } = this.props;
    return (
      <div className={styles["progress-bar"]}>
        <div className={styles["progress-bar-wrapper"]}>
          <ul className={styles["progress-bar-items"]}>
            {links
              ? links.map(link => (
                  <li
                    className={styles["progress-bar-item"]}
                    onClick={this.goToPath.bind(this, link.path)}
                  >
                    <span
                      className={classnames(
                        styles["progress-bar-link"],
                        {[styles["active"]]: link.active},
                        {[styles["prev"]]: link.prevActive}
                      )}
                    >
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
const mapStateToProps = () => ({});

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(ProgressBar);
