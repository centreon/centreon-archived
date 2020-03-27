import React from 'react';

import { makeStyles, Breadcrumbs } from '@material-ui/core';
import NavigateNextIcon from '@material-ui/icons/NavigateNext';

import BreadcrumbLink from './Link';

const useStyles = makeStyles({
  breadcrumb: {
    padding: '4px 16px',
  },
  item: {
    display: 'flex',
  },
});

interface Link {
  label: string;
  index: number;
}

interface Props {
  links: Array<Link>;
}

const Breadcrumb = ({ links }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <Breadcrumbs
      classes={{ root: classes.breadcrumb, li: classes.item }}
      separator={<NavigateNextIcon fontSize="small" />}
      aria-label="Breadcrumb"
    >
      {links &&
        links.map((link, index) => (
          <BreadcrumbLink
            key={`${link.label}${link.index}`}
            breadcrumb={link}
            index={index}
            count={links.length}
          />
        ))}
    </Breadcrumbs>
  );
};

export default Breadcrumb;
