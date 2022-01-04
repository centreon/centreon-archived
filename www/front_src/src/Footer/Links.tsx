import * as React from 'react';

import { dec, equals, length, not, pipe } from 'ramda';
import { Link, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

const useStyles = makeStyles((theme) => ({
  lastLink: {
    padding: theme.spacing(0, 2),
  },
  link: {
    padding: theme.spacing(0, 2),
  },
  linkWithSeperator: {
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'center',
  },
  links: {
    alignItems: 'center',
    display: 'grid',
    gridTemplateColumns: 'repeat(7, auto)',
    height: '100%',
    justifyContent: 'center',
  },
}));

interface FooterLink {
  title: string;
  url: string;
}

const links: Array<FooterLink> = [
  { title: 'Documentation', url: 'https://docs.centreon.com/' },
  { title: 'Support', url: 'https://support.centreon.com/' },
  { title: 'Centreon', url: 'https://www.centreon.com/' },
  { title: 'Github Project', url: 'https://github.com/centreon/centreon.git' },
  { title: 'The Watch', url: 'https://thewatch.centreon.com/' },
  { title: 'Slack', url: 'https://centreon.github.io/' },
  {
    title: 'Security Issue',
    url: 'https://github.com/centreon/centreon/security/policy',
  },
];

const numbersOfLinks = pipe<Array<FooterLink>, number, number>(
  length,
  dec,
)(links);

const Links = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.links}>
      {links.map(({ title, url }, idx) => {
        const isLastElement = equals(idx, numbersOfLinks);

        return (
          <div className={classes.linkWithSeperator} key={title}>
            <div className={isLastElement ? classes.lastLink : classes.link}>
              <Typography variant="body2">
                <Link
                  color="inherit"
                  href={url}
                  rel="noopener norefferer"
                  target="_blank"
                >
                  {title}
                </Link>
              </Typography>
            </div>
            <Typography variant="body2">{not(isLastElement) && '|'}</Typography>
          </div>
        );
      })}
    </div>
  );
};

export default Links;
