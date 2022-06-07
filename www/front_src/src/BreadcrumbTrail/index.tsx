import { useMemo } from 'react';

import { useAtomValue } from 'jotai/utils';
import { isNil } from 'ramda';

import { Breadcrumbs as MuiBreadcrumbs } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import NavigateNextIcon from '@mui/icons-material/NavigateNext';

import navigationAtom from '../Navigation/navigationAtoms';

import { Breadcrumb as BreadcrumbModel, BreadcrumbsByPath } from './models';
import Breadcrumb from './Breadcrumb';
import getBreadcrumbsByPath from './getBreadcrumbsByPath';

const useStyles = makeStyles({
  item: {
    display: 'flex',
  },
  root: {
    padding: '4px 16px',
  },
});

interface Props {
  breadcrumbsByPath: BreadcrumbsByPath;
  path: string;
}

const getBreadcrumbs = ({
  breadcrumbsByPath,
  path,
}): Array<BreadcrumbModel> => {
  if (breadcrumbsByPath[path]) {
    return breadcrumbsByPath[path];
  }

  if (path.includes('/')) {
    const shorterPath = path.split('/').slice(0, -1).join('/');

    return getBreadcrumbs({ breadcrumbsByPath, path: shorterPath });
  }

  return [];
};

const BreadcrumbTrail = ({ breadcrumbsByPath, path }: Props): JSX.Element => {
  const classes = useStyles();

  const breadcrumbs = useMemo(
    () => getBreadcrumbs({ breadcrumbsByPath, path }),
    [breadcrumbsByPath, path],
  );

  return (
    <MuiBreadcrumbs
      aria-label="Breadcrumb"
      classes={{ li: classes.item, root: classes.root }}
      separator={<NavigateNextIcon fontSize="small" />}
    >
      {breadcrumbs.map((breadcrumb, index) => (
        <Breadcrumb
          breadcrumb={breadcrumb}
          key={breadcrumb.label}
          last={index === breadcrumbs.length - 1}
        />
      ))}
    </MuiBreadcrumbs>
  );
};

const Breadcrumbs = ({ path }: Pick<Props, 'path'>): JSX.Element | null => {
  const navigation = useAtomValue(navigationAtom);

  if (isNil(navigation)) {
    return null;
  }

  return (
    <BreadcrumbTrail
      breadcrumbsByPath={getBreadcrumbsByPath(navigation.result)}
      path={path}
    />
  );
};

export default Breadcrumbs;
