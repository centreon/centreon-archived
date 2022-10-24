import makeStyles from '@mui/styles/makeStyles';

import miniLogoLight from './Centreon_Logo_Blanc_C.svg';

interface Props {
  onClick?: () => void;
}
const useStyles = makeStyles((theme) => ({
  miniLogo: {
    height: theme.spacing(5),
    width: theme.spacing(3)
  }
}));

const MiniLogo = ({ onClick }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div aria-hidden onClick={onClick}>
      <img alt="mini logo" className={classes.miniLogo} src={miniLogoLight} />
    </div>
  );
};

export default MiniLogo;
