<!DOCTYPE html>
  <html>
    <head>
    <link rel="icon" type="image/x-icon" href="./img/logo.ico">
      <title>Polmlek - Doceniamy pomysły</title>
      <link rel="stylesheet" type="text/css" href="css/wzor.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
      
      <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
      <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lato:ital@1&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@300&display=swap" rel="stylesheet">
    </head>

  <body>
  <?php
  header('Cache-Control: no cache'); //no cache
  session_cache_limiter('private_no_expire'); // works
  $date = new DateTime(date('Y-m-d'));
  session_start();
  $typ=$_SESSION['typ'];
  if($typ==1)
  {
    header("Location: glowna.php");
  }
  require_once "menu.php";
  ?>
  
  <div>
      <div id='spacja'>
      </div>
    <?php
      if ($polaczenie->connect_errno!=0)
      {
          echo "Error: ".$polaczenie->connect_errno;
      }
      else
      {
        $id = $_SESSION['id'];

        $id = htmlentities($id, ENT_QUOTES, "UTF-8");

        $sql = "SELECT rejestr.id_rejestr AS idr, rejestr.zmiana, rejestr.imie as imi, rejestr.nazwiwsko as nazw, rejestr.dzial_id, rejestr.inni_analizujacy, rejestr.blokada, rejestr.termin_kierownicy, rejestr.termin_opiniarze, rejestr.opis_krotki, rejestr.kierownik_wstrzymano, uzytkownicy.id AS id_us, rejestr.dzial_id, (SELECT COUNT(opinie.tresc) FROM opinie WHERE opinie.pomysl_id = rejestr.id_rejestr AND opinie.user_id = $id) AS spr, dzial.nazwa, rejestr.imie_realizator AS imi, rejestr.nazwisko_realizator AS nazw, (SELECT COUNT(tresc) FROM opinie WHERE pomysl_id = idr) AS ile_opi, rejestr.korzysci, uzytkownicy.imie, uzytkownicy.nazwisko, rejestr.opis_dlugi, rejestr.data_wplywu, rejestr.konto_dzial, stan.stan FROM rejestr JOIN stan ON rejestr.stan_id = stan.id JOIN uzytkownicy ON uzytkownicy.id = rejestr.uzytkownik_id JOIN dzial ON dzial.id = rejestr.dzial_id WHERE rejestr.stan_id = 1 ORDER BY rejestr.zmiana DESC LIMIT 5";

        /*if($typ == 3)
        {
          $sql.=" AND dzial.id = ".$dzial;
        }*/
        //$sql.=" GROUP BY rejestr.zmiana DESC;";
        if ($rezultat = @$polaczenie->query(
        sprintf($sql,
        mysqli_real_escape_string($polaczenie,$id))))
        {
          $ilu_userow = $rezultat->num_rows;
            
          if($ilu_userow>0)
          {
            
            echo "<table class='analizatable' style='width: 100%; zoom: 76%' border><tr id='nowhite'><th>Nr pomysłu</th><th  width='25%'>Opis krótki</th><th>Imię i nazwisko pracownika/ów</th><th>Pliki</th><th>Stan</th><th>Działanie</th><th>Ilość dni do końca anazliy pomysłu</th><th>Opinie</th><th>Więcej informacji o pomyśle</th></tr>";
            while($wiersz = $rezultat->fetch_assoc())
            {
              $ludzie=$wiersz['inni_analizujacy'];
                    if($ludzie!='')
                    {
                      $ludzie = explode(";", $ludzie);
                    }
                    else
                    {
                      $ludzie=[''];
                    }
                    if(($wiersz['dzial_id']==$_SESSION['dzial'] and $typ==3) OR ($typ == 2 OR $typ == 6 OR $typ == 4 OR $typ == 5 OR $typ == 9) OR (in_array($_SESSION['id'], $ludzie)))
                    {
                      $zmiana=strtotime($wiersz['zmiana']);
                    if($aktywnosc>$zmiana)
                    {
                      echo "<tr>";
                    }
                    else
                    {
                      echo "<tr style='background-color: #ff66cc'>";
                    }
              echo "<td>",$wiersz['idr'],"</td><td style='word-break: break-word;'>",$wiersz['opis_krotki'],"</td><td>";
              if($wiersz['konto_dzial']==0)
              {
                echo $wiersz['imie']," ",$wiersz['nazwisko'];
              }
              else
              {
                
                echo $wiersz['imi']," ",$wiersz['nazw'];
              }
              echo"</td><td>";
              if ($rezultat6 = @$polaczenie->query(
                sprintf("SELECT * FROM dzial, rejestr WHERE rejestr.id_rejestr=".$wiersz['idr']." AND rejestr.dzial_id=dzial.id")))
                {
                  $ilu_userow6 = $rezultat6->num_rows;
                  if($ilu_userow6>0)
                  {
                    while($wiersz6 = $rezultat6->fetch_assoc())
                    {
                      
                      $blokada=$wiersz6['blokada'];
                    }
                  }
                }
              if(is_dir("./files/".$wiersz['idr']))
              {
                $files1=scandir("./files/".$wiersz['idr']);
                $ile=count($files1);
                if($ile>1)
                {
                  for($i=2; $i<$ile; $i++)
                  {
                    echo"<a href='./files/".$wiersz['idr']."/".$files1[$i]."' download><p style='font-size: 1vw;'>".$files1[$i]."</p></a><br />";
                  }
                }
              }
              echo"<form action='./tfpdf/pdf.php' method='POST' target='_blank'>";
              echo"<input type='hidden' value='".$wiersz['idr']."' name='idk'>";
              echo"<button type='submit' class='buttonp'>
              <span class='button__text'>Raport PDF</span>
              <span class='button__icon'>
              <i class='bx bxs-file-pdf'></i>
              </span></button></form>";
              echo"</form>";
              
              
              echo "</td><td>",$wiersz['stan'],"</td><td>";
              if(($typ==9) AND $wiersz['blokada']!=0)
              {
                echo"<form action='odblokuj.php' method='POST'>
                <button type='submit'  class='buttonzod' style='width: 100%;, margin-top:10px;' value='".$wiersz['idr']."' name='pom'>
                <span class='button__textu' style='font-size: 0.8vw' >Odblokuj</span>
                <span class='button__iconu'>
                <i class='bx bx-undo'></i>
                </span></button></form><br/>";
              }
              else if(($typ == 3 or $typ == 6 or $typ == 5) AND $wiersz['blokada']!=0)
              {
                echo"Pomysł Zablokowany przez prezesa/koorydynatora pomysłu";
              }
              if(($typ == 3 or $typ == 6) AND $wiersz['kierownik_wstrzymano']==0 && $wiersz['blokada']==0 && ($_SESSION['dzial']==$wiersz['dzial_id'] OR $typ==6))
              {
                echo "<form action='zatwierdz.php' method='POST'><input type='hidden' name='pom' value='".$wiersz['idr']."' /><button type='submit' class='buttond'>
                <span class='button__text' style='font-size: 1vw'>Zatwierdź/odrzuć pomysł</span>
                <span class='button__icon'>
                <i class='bx bx-dots-horizontal-rounded'></i>
                </span></button></form>";
              }
              else if(($typ == 3 or $typ == 6) AND $wiersz['kierownik_wstrzymano']!=0 AND ($_SESSION['dzial']==$wiersz['dzial_id'] OR $typ==6))
              {
                echo "<form action='zatwierdz2.php' method='POST'>
                <input type='hidden' name='id' value='",$wiersz['idr'],"' />
                <input type='hidden' name='ids' value='",$wiersz['idr'],"' />
                <input type='hidden' name='id_user' value='",$wiersz['id_us'],"' />
                <button type='submit' class='buttong' name='przycisk' value='wznow'  >
                    <span class='button__text'>ㅤㅤWznów ㅤㅤㅤ</span>
                    <span class='button__icon'>
                        <i class='bx bx-check'></i>
                    </span>
                </button></form>";
                  echo"
                  <button type='button' class='buttonl' onclick='usunRekordd1(".'"abc"'.")' name='przycisk' value='Odrzuć pomysł' >
                      <span class='button__text'>Odrzuć pomysł ㅤ    </span>
                      <span class='button__icon'>
                          <i class='bx bx-x'></i>  
                      </span>
                  </button>
                  <div id='okienkoo1' style='display:none; width: 50%'>
            <button class='bbtt1' style='margin-left: -90%; margin-top: -15%;' type='button' onclick='anulujj1()'>X</button><br/>
            Podaj powód odrzucenia pomysłu
            <form action='zatwierdz2.php' method='POST'>
              <textarea required name='opinia' style='font-size: 12px'></textarea>
              <input type='hidden' name='przycisk' value='Odrzuć pomysł'><br/>
              <input type='hidden' name='id' value=''.$idr.'' />
              <button class='bbtt' type='submit' >Odrzuć pomysł</button>
            </form>
            
            
          </div>
          <style>
          #okienko-tlo {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(128, 128, 128, 0.5);
            z-index: 9998;
            backdrop-filter: blur(10px);
          }
          
          #okienkoo1 {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid black;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            text-align: center;
            
          }
          
          #okienkoo1 button {
            margin: 10px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            font-size: 16px;
            cursor: pointer;
          }
          
          #okienkoo1 button:hover {
            background-color: #f2f2f2;
          }
          </style>
          <script>
          
          function usunRekordd1(idd) {
          var okienkoo1 = document.getElementById('okienkoo1');
          okienkoo1.style.display = 'block';
          okienkoo1.dataset.id = idd;
        }
        
        function potwierdzz1() {
          var okienkoo1 = document.getElementById('okienkoo1');
          var idd = okienkoo1.dataset.id;
          var formm = document.getElementById('form-' + idd);
          window.history.back();
        }
        
        function anulujj1() {
          var okienkoo1 = document.getElementById('okienkoo1');
          okienkoo1.style.display = 'none';
        }
        
          </script>
                ";
              }
              else if((in_array($_SESSION['id'], $ludzie) OR $typ == 2 OR $typ == 4 OR $typ == 5 OR $typ == 9) AND $wiersz['spr'] == 0 AND $wiersz['kierownik_wstrzymano']==0 AND $blokada==0)
              {
                
                echo "<form action='opinia.php' method='POST'><input type='hidden' name='pom' value='".$wiersz['idr']."' />
                <button type='submit' class='buttonopi'>
                <span class='button__text' style='font-size: 1vw'>Dodaj opinię</span>
                <span class='button__icon'>
                <i class='bx bx-plus'></i>
                </span>
                </button>
               </form>";
               if($typ==9 OR $typ==5)
               {
                echo"<form action='zablokuj.php' method='POST'>
                <button type='submit'  class='buttonzab' style='width: 100%;, margin-top:10px;' value='".$wiersz['idr']."' name='pom'>
                <span class='button__textu' style='font-size: 0.8vw' >Odrzuć</span>
                <span class='button__iconu'>
                <i class='bx bx-block'></i>
                </span></button></form><br/>";
               }          
              }
              else if(($typ == 2 OR $typ == 4 OR $typ == 5 OR $typ == 9 OR in_array($_SESSION['id'], $ludzie)) AND $wiersz['spr'] == 0 AND $wiersz['kierownik_wstrzymano']==1)
              {
                echo "Pomysł wstrzymany przez kierownika działu";
              }
              
              else if(($typ == 2 OR $typ == 4 OR $typ == 5 OR $typ == 9 OR in_array($_SESSION['id'], $ludzie)) AND $wiersz['spr'] == 1)
              {
                echo "Oczekiwanie na decyzję kierownika";
                if($typ==9 && $blokada==0)
               {
                //echo $blokada;
                echo"<form action='zablokuj.php' method='POST'>
                <button type='submit'  class='buttonzab' style='width: 100%;, margin-top:10px;' value='".$wiersz['idr']."' name='pom'>
                <span class='button__textu' style='font-size: 0.8vw' >Odrzuć</span>
                <span class='button__iconu'>
                <i class='bx bx-block'></i>
                </span></button></form><br/>";
               }
               
               
              }
              echo"</td><td>";
                    if($typ==3 or $typ==6)
                    {
                      $date2 = new DateTime($wiersz['termin_kierownicy']);
                    }
                    else
                    {
                      $date2 = new DateTime($wiersz['termin_opiniarze']);
                    }
                    if($wiersz['kierownik_wstrzymano']!=0 OR $blokada!=0)
                    {
                      echo "Wstrzymano";
                    }
                    else if($date <= $date2)
                      {
                        $interval = date_diff($date,$date2);
                        $interval=$interval->days;
                        echo $interval;
                      }
                      else
                      {
                        $interval = date_diff($date,$date2);
                        $interval=$interval->days;
                        echo "<p style='color: red; font-size: 0.9vw'>Po terminie: ".$interval.'</p>';
                      }
                  
                    echo"</td><td>";
              if($typ == 1)
              {
                echo"Nie masz uprawnień do przeglądania opinii";
                
              }
              else
              {
                echo"<div class='tekst'> Ilość opinii o danym pomyśle: ".$wiersz['ile_opi']."</div>";
                if($wiersz['ile_opi']>0)
                  {
                  echo"<br/><form action='zobacz_opi.php' method='POST'><button type='submit' class='buttonpo' name='pom' value='".$wiersz['idr']."'><span class='button__text'>Przejrzyj opinie</span>
                  <span class='button__icon'>
                  <i class='bx bx-dots-horizontal-rounded'></i>
                  </span></button>";
                  }
              }
            echo"</form>";
            echo"</td><td>";
            $idrrr=$wiersz['idr'];
            if ($rezultat0 = @$polaczenie->query(
              sprintf("SELECT rejestr.opis_krotki, rejestr.opis_dlugi, rejestr.korzysci, rejestr.data_wplywu, rejestr.konto_dzial, rejestr.imie_realizator AS dzialimie, rejestr.nazwisko_realizator AS dzialnazwisko, dzial.nazwa, stan.stan, uzytkownicy.imie, uzytkownicy.nazwisko, uzytkownicy.telefon FROM rejestr, uzytkownicy, dzial, stan WHERE id_rejestr=$idrrr AND rejestr.uzytkownik_id=uzytkownicy.id AND rejestr.dzial_id=dzial.id AND rejestr.stan_id=stan.id;")))
              {
                  $ilu_userow0 = $rezultat0->num_rows;
                  if($ilu_userow0>0)
                  {
                    while($wiersz0 = $rezultat0->fetch_assoc())
                    {
                      $krotki=$wiersz0['opis_krotki'];
                      $dlugi=$wiersz0['opis_dlugi'];
                      $korzysci=$wiersz0['korzysci'];
                      $data_wplywu=$wiersz0['data_wplywu'];
                      $kontodzial=$wiersz0['konto_dzial'];
                      $dzialimie=$wiersz0['dzialimie'];
                      $dzialnazwisko=$wiersz0['dzialnazwisko'];
                      $nazwa=$wiersz0['nazwa'];
                      $stan=$wiersz0['stan'];
                      $imie=$wiersz0['imie'];
                      $nazwisko=$wiersz0['nazwisko'];
                      $telefon=$wiersz0['telefon'];
                    }
                  }
              }
            require "info.php";
            echo"</td>
            </tr>";
              
            }
          }
          }
           echo"</table>";         
        }
      }
    ?>
    </div>
    <?php
        require_once "fotter.php";
    ?>
    <script>
          const targetDiv = document.getElementById("info");
          const btn = document.getElementById("opcja1");
          btn.onclick = function () {
            if (targetDiv.style.display !== "none") {
              targetDiv.style.display = "none";
            } else {
              targetDiv.style.display = "block";
            }
          };
    </script>
</body>
</html>