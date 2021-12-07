import * as React from 'react';

import classnames from 'classnames';
import { useLocation, useNavigate } from 'react-router-dom';
import { isNil, replace } from 'ramda';

import { PageSkeleton } from '@centreon/ui';

import styles from '../../Header/header.scss';

const LegacyRoute = (): JSX.Element => {
  const [loading, setLoading] = React.useState(true);
  const mainContainerRef = React.useRef<HTMLElement | null>(null);
  const location = useLocation();
  const navigate = useNavigate();

  React.useEffect(() => {
    mainContainerRef.current =
      window.document.getElementById('fullscreen-wrapper');
  }, []);

  const load = (): void => {
    setLoading(false);

    window.frames[0].document.querySelectorAll('a').forEach((element) => {
      element.addEventListener(
        'click',
        (e) => {
          e.preventDefault();
          const href = (e.target as HTMLLinkElement).getAttribute('href');

          if (isNil(href)) {
            return;
          }

          navigate(replace('./', '/', href), { replace: true });
        },
        { once: true },
      );
    });
  };

  const { search, hash } = location;

  const params = (search || '') + (hash || '');

  return (
    <>
      {loading && <PageSkeleton />}
      <iframe
        className={classnames({ [styles.hidden as string]: loading })}
        frameBorder="0"
        id="main-content"
        scrolling="yes"
        src={`./main.get.php${params}`}
        style={{ height: '100%', width: '100%' }}
        title="Main Content"
        onLoad={load}
      />
    </>
  );
};

export default LegacyRoute;
